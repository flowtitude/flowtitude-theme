<?php
if (!defined('ABSPATH')) exit;

/**
 * Endpoint para gestionar carpetas de snippets
 * Este archivo maneja la creación y listado de carpetas para snippets personalizados
 */

// Definir la función para obtener el directorio personalizado si no existe
if (!function_exists('flowtitude_get_custom_dir')) {
    /**
     * Obtiene (y crea si es necesario) el directorio personalizado para un tipo dado (snippets, bricks, etc).
     *
     * @param string $type
     * @return string Ruta absoluta del directorio
     */
    function flowtitude_get_custom_dir($type = 'snippets') {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'] . '/flowtitude';
        
        if (!file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
            chmod($base_dir, 0775); // Otorgar permisos de escritura al grupo
        }
        
        $type_dir = $base_dir . '/' . $type;
        if (!file_exists($type_dir)) {
            wp_mkdir_p($type_dir);
            chmod($type_dir, 0775); // Otorgar permisos de escritura al grupo
        }
        
        return $type_dir;
    }
}

// Helper para validar nombres de carpetas de snippets
if (!function_exists('flowtitude_validate_folder_name')) {
    function flowtitude_validate_folder_name($name) {
        if (!is_string($name) || empty($name)) return false;
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) return false;
        if ($name === '.' || $name === '..' || strpos($name, '/') !== false || strpos($name, '\\') !== false) return false;
        if (preg_match('/[\\/:*?"<>|]/', $name)) return false;
        $reserved_names = ['utils', '.DS_Store', 'node_modules', '.git'];
        if (in_array(strtolower($name), $reserved_names)) return false;
        return true;
    }
}

// Añadir manejo de errores global para este archivo
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log("Error: $errstr in $errfile on line $errline", 'error', 'error_handler');
    }
    return true;
}, E_ALL);

// Registrar los endpoints REST API
add_action('rest_api_init', function () {
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Registrando endpoints REST API de snippet folders', 'info');
    }
    
    // Endpoint para listar carpetas
    register_rest_route('flowtitude/v1', '/snippet-folders', [
        'methods' => 'GET',
        'callback' => 'flowtitude_list_snippet_folders',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [],
    ]);
    
    // Endpoint para crear nuevas carpetas
    register_rest_route('flowtitude/v1', '/snippet-folders', [
        'methods' => 'POST',
        'callback' => 'flowtitude_create_snippet_folder',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => [
            'folder_name' => [
                'required' => true,
                'type' => 'string',
                'validate_callback' => function($param) { return flowtitude_validate_folder_name($param); }
            ],
        ],
    ]);
    
    // Solo log si el logging está activado
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Endpoints REST API registrados correctamente', 'info', 'endpoints');
    }
});

// Añadir un hook para verificar que WordPress está cargando el archivo
add_action('init', function() {
    // Solo log si el logging está activado
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Plugin inicializado', 'info', 'init');
    }
});

/**
 * Lista las carpetas de snippets disponibles.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function flowtitude_list_snippet_folders(WP_REST_Request $request) {
    try {
        // Verificar el nonce usando la función correcta para REST API
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nonce inválido'
            ], 401);
        }

        $folders = flowtitude_get_snippet_folders();
        
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Listado de carpetas de snippets exitoso. Total: ' . count($folders), 'info');
        }
        
        return new WP_REST_Response([
            'success' => true,
            'folders' => $folders,
            'message' => 'Carpetas listadas correctamente'
        ], 200);
    } catch (Exception $e) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Error al listar carpetas de snippets: ' . $e->getMessage(), 'error');
        }
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Error al listar carpetas: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Crea una nueva carpeta para snippets personalizados.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function flowtitude_create_snippet_folder(WP_REST_Request $request) {
    try {
        // Verificar el nonce usando la función correcta para REST API
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nonce inválido'
            ], 401);
        }

        $params = $request->get_json_params();
        
        if (empty($params['folder_name'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Se requiere un nombre de carpeta'
            ], 400);
        }
        
        // Sanitizar el nombre de la carpeta
        $folder_name = sanitize_file_name($params['folder_name']);
        
        // Verificar que el nombre sea válido
        if (!flowtitude_validate_folder_name($folder_name)) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Intento de crear carpeta con nombre inválido: ' . $folder_name, 'warning');
            }
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Nombre de carpeta no válido'
            ], 400);
        }
        
        // Obtener y verificar el directorio base
        $base_dir = flowtitude_get_custom_dir('snippets');
        if (!$base_dir) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Directorio de snippets no encontrado: ' . $base_dir, 'warning');
            }
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se pudo obtener el directorio base'
            ], 500);
        }
        
        // Crear la carpeta
        $folder_path = $base_dir . '/' . $folder_name;
        if (file_exists($folder_path)) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Intento de crear carpeta ya existente: ' . $folder_path, 'warning');
            }
            return new WP_REST_Response([
                'success' => false,
                'message' => 'La carpeta ya existe'
            ], 409);
        }
        
        if (!wp_mkdir_p($folder_path)) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Fallo al crear la carpeta de snippets: ' . $folder_name, 'error');
            }
            return new WP_REST_Response([
                'success' => false,
                'message' => 'No se pudo crear la carpeta'
            ], 500);
        }
        
        // Obtener la lista actualizada de carpetas
        $folders = flowtitude_get_snippet_folders();
        
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Carpeta de snippets creada: ' . $folder_name, 'success');
        }
        
        return new WP_REST_Response([
            'success' => true,
            'folders' => $folders,
            'message' => 'Carpeta creada correctamente'
        ], 201);
        
    } catch (Exception $e) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Error al crear la carpeta de snippets: ' . $e->getMessage(), 'error');
        }
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Error al crear la carpeta: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Función auxiliar para obtener la lista de carpetas de snippets.
 *
 * @return array
 */
function flowtitude_get_snippet_folders() {
    $base_dir = flowtitude_get_custom_dir('snippets');
    $folders = ['custom']; // Siempre incluir la carpeta custom
    
    if (is_dir($base_dir)) {
        foreach (new DirectoryIterator($base_dir) as $item) {
            if ($item->isDot() || !$item->isDir()) continue;
            
            $name = $item->getFilename();
            if ($name !== 'utils' && $name !== '.DS_Store' && $name !== 'custom') {
                $folders[] = $name;
            }
        }
    }
    
    return array_values(array_unique($folders));
}

