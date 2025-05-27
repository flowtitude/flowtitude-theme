<?php
if (!defined('ABSPATH')) exit;

/**
 * Carga los snippets y componentes activos seleccionados desde el panel.
 *
 * @return void
 */
function flowtitude_load_active_snippets_and_bricks() {
    // Cargar snippets activos
    flowtitude_load_active_snippets();

    // Cargar componentes activos de Bricks
    flowtitude_load_active_bricks();
}
add_action('after_setup_theme', 'flowtitude_load_active_snippets_and_bricks');

/**
 * Carga los snippets activos seleccionados desde el panel.
 *
 * @return void
 */
function flowtitude_load_active_snippets() {
    // Primero cargar snippets del sistema
    $system_snippets = [
        'placeholder.php', // Generador de placeholders
        // Añadir más snippets del sistema aquí
    ];

    foreach ($system_snippets as $file) {
        $path = FLOWTITUDE_DIR . '/snippets/' . $file;
        if (file_exists($path)) {
            include_once $path;
        }
    }

    // Luego cargar snippets personalizados activos
    $snippet_dir = flowtitude_get_custom_dir('snippets');
    $active_snippets = get_option('flowtitude_active_snippets', []);

    if (!is_array($active_snippets)) return;

    foreach ($active_snippets as $file) {
        // Evitar que cargue archivos de bricks (en caso de que queden referencias)
        if (str_starts_with($file, 'bricks/')) continue;
        
        // Preservar la estructura de carpetas
        $path = $snippet_dir . '/' . trim($file, '/');
        
        // Verificar que el archivo está dentro del directorio permitido
        $real_path = realpath($path);
        $real_snippet_dir = realpath($snippet_dir);
        
        if ($real_path && file_exists($real_path) && strpos($real_path, $real_snippet_dir) === 0) {
            include_once $real_path;
            flowtitude_debug_log("Snippet cargado correctamente: {$real_path}", 'success');
        } else {
            flowtitude_debug_log("No se pudo cargar el snippet: {$file}", 'warning');
            flowtitude_debug_log("Ruta real: {$real_path}", 'debug');
            flowtitude_debug_log("Directorio base: {$real_snippet_dir}", 'debug');
        }
    }
}

/**
 * Carga los componentes activos de Bricks
 */
function flowtitude_load_active_bricks() {
    error_log('Flowtitude: Iniciando carga de componentes activos de Bricks');
    // Registro de rutas REST para BRICKS
    add_action('rest_api_init', function () {
        register_rest_route('flowtitude/v1', '/bricks', [
            'methods' => 'GET',
            'callback' => function () {
                try {
                    error_log('Flowtitude: Iniciando llamada al endpoint GET /bricks');
                    
                    if (!current_user_can('manage_options')) {
                        error_log('Flowtitude: Usuario no tiene permisos para acceder a /bricks');
                        return new WP_Error('forbidden', 'No tienes permisos para acceder a este endpoint', ['status' => 403]);
                    }
                    
                    $response = flowtitude_get_bricks_components();
                    
                    if (is_wp_error($response)) {
                        error_log('Flowtitude: Error al obtener componentes: ' . $response->get_error_message());
                        return $response;
                    }
                    
                    error_log('Flowtitude: Respuesta del endpoint /bricks: ' . print_r($response, true));
                    return rest_ensure_response($response);
                    
                } catch (Exception $e) {
                    error_log('Flowtitude: Excepción en endpoint /bricks: ' . $e->getMessage());
                    return new WP_Error('server_error', 'Error interno del servidor', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);

        register_rest_route('flowtitude/v1', '/bricks', [
            'methods' => 'POST',
            'callback' => function ($request) {
                try {
                    error_log('Flowtitude: Iniciando llamada al endpoint POST /bricks');
                    
                    if (!current_user_can('manage_options')) {
                        error_log('Flowtitude: Usuario no tiene permisos para acceder a POST /bricks');
                        return new WP_Error('forbidden', 'No tienes permisos para acceder a este endpoint', ['status' => 403]);
                    }
                    
                    $params = $request->get_json_params();
                    if (!is_array($params)) {
                        return new WP_Error('invalid_params', 'Parámetros inválidos', ['status' => 400]);
                    }
                    
                    $result = flowtitude_save_bricks_components($params);
                    
                    if (is_wp_error($result)) {
                        error_log('Flowtitude: Error al guardar componentes: ' . $result->get_error_message());
                        return $result;
                    }
                    
                    return rest_ensure_response(['success' => true]);
                    
                } catch (Exception $e) {
                    error_log('Flowtitude: Excepción en endpoint POST /bricks: ' . $e->getMessage());
                    return new WP_Error('server_error', 'Error interno del servidor', ['status' => 500]);
                }
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);
    });

    // Carga de componentes activos
    $bricks_dir = flowtitude_get_custom_dir('bricks');
    $active_components = get_option('flowtitude_active_bricks', []);
    
    if (!is_array($active_components)) {
        $active_components = [];
        error_log('Flowtitude: No hay componentes activos de Bricks o el formato es incorrecto');
        return;
    }
    
    error_log('Flowtitude: Cargando ' . count($active_components) . ' componentes activos de Bricks');
    
    foreach ($active_components as $file) {
        // Preservar la estructura de carpetas
        $path = $bricks_dir . '/' . trim($file, '/');
        $real_path = realpath($path);
        
        // Si el archivo no existe en la ruta directa, verificar si tiene información de carpeta
        if (!$real_path || !file_exists($real_path)) {
            $component_types = ['custom-elements', 'dynamic-tags', 'conditionals'];
            $found = false;
            
            foreach ($component_types as $type) {
                $type_path = $bricks_dir . '/' . $type . '/' . basename($file);
                if (file_exists($type_path)) {
                    $path = $type_path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                error_log('Flowtitude: No se pudo encontrar el componente de Bricks: ' . $file);
                continue;
            }
        }
        
        // Verificar que el archivo está dentro del directorio permitido
        $real_path = realpath($path);
        $real_bricks_dir = realpath($bricks_dir);
        
        if ($real_path && file_exists($real_path) && strpos($real_path, $real_bricks_dir) === 0) {
            error_log('Flowtitude: Cargando componente de Bricks: ' . $real_path);
            include_once $real_path;
        } else {
            error_log('Flowtitude: No se pudo cargar el componente de Bricks: ' . $path);
            error_log("Flowtitude: Ruta real: {$real_path}");
            error_log("Flowtitude: Directorio base: {$real_bricks_dir}");
        }
    }
}

/**
 * Obtiene los componentes de Bricks disponibles y activos
 * @return array Array con los componentes disponibles y activos
 */
function flowtitude_get_bricks_components() {
    try {
        $bricks_dir = flowtitude_get_custom_dir('bricks');
        $active_components = get_option('flowtitude_active_bricks', []);
        
        if (!is_array($active_components)) {
            $active_components = [];
        }
        
        $component_types = ['custom-elements', 'dynamic-tags', 'conditionals'];
        $components = [];
        
        foreach ($component_types as $type) {
            $type_dir = $bricks_dir . '/' . $type;
            $components[$type] = [];
            
            if (!file_exists($type_dir)) {
                wp_mkdir_p($type_dir);
                continue;
            }
            
            $files = glob($type_dir . '/*.php');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $relative_path = $type . '/' . $filename;
                
                // Leer el contenido del archivo para extraer título y descripción
                $content = file_get_contents($file);
                $title = $filename;
                $description = '';
                
                // Extraer título, descripción y carpeta del comentario del archivo
                // Primero intentar con comentarios de una línea (estilo // Título)
                $lines = explode("\n", $content);
                $folder = ''; // Valor por defecto para la carpeta
                
                foreach ($lines as $index => $line) {
                    if (preg_match('/^\/\/\s*(.+)$/', trim($line), $matches)) {
                        $title = trim($matches[1]);
                        
                        // Buscar la siguiente línea para la descripción
                        if (isset($lines[$index + 1]) && preg_match('/^\/\/\s*(.+)$/', trim($lines[$index + 1]), $desc_matches)) {
                            $description = trim($desc_matches[1]);
                            
                            // Buscar la tercera línea para la carpeta
                            if (isset($lines[$index + 2]) && preg_match('/^\/\/\s*(.+)$/', trim($lines[$index + 2]), $folder_matches)) {
                                $folder = trim($folder_matches[1]);
                            }
                        }
                        break;
                    }
                }
                
                // Si no se encuentra en comentarios de una línea, intentar con DocBlock
                if ($title === $filename) {
                    if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\s*\n/s', $content, $matches)) {
                        $title = trim($matches[1]);
                    }
                    
                    // Buscar descripción en el segundo comentario
                    if (preg_match('/\*\s*(.+?)\s*\n\s*\*\s*(.+?)\s*\n/s', $content, $matches)) {
                        $description = trim($matches[1]);
                        
                        // Buscar carpeta en el tercer comentario
                        if (isset($matches[2])) {
                            $folder = trim($matches[2]);
                        }
                    } else if (preg_match('/\*\s*(.+?)\s*\n\s*\*\//s', $content, $matches)) {
                        $description = trim($matches[1]);
                    }
                }
                
                // Validar que la carpeta especificada sea una de las permitidas
                $allowed_folders = ['custom-elements', 'dynamic-tags', 'conditionals'];
                
                // Si se especificó una carpeta en el comentario, verificar que sea válida
                if (!empty($folder) && !in_array($folder, $allowed_folders)) {
                    // Si la carpeta no es válida, ignorar este componente y continuar con el siguiente
                    error_log('Flowtitude: Carpeta no válida especificada en el componente ' . $filename . ': ' . $folder);
                    continue;
                }
                
                // Usar la carpeta especificada o el tipo predeterminado
                $target_type = !empty($folder) ? $folder : $type;
                
                // Asegurarse de que existe la categoría en el array de componentes
                if (!isset($components[$target_type])) {
                    $components[$target_type] = [];                    
                }
                
                $components[$target_type][] = [
                    'file' => $relative_path,
                    'title' => $title,
                    'description' => $description,
                    'folder' => $folder, // Guardar la carpeta especificada
                    'active' => in_array($relative_path, $active_components)
                ];
            }
        }
        
        return [
            'components' => $components,
            'active' => $active_components
        ];
    } catch (Exception $e) {
        error_log('Flowtitude: Error al obtener componentes de Bricks: ' . $e->getMessage());
        return new WP_Error('error', 'Error al obtener componentes', ['status' => 500]);
    }
}

/**
 * Guarda los componentes activos de Bricks
 * @param array $active_components Array con los componentes activos
 * @return bool|WP_Error True si se guardó correctamente, WP_Error en caso contrario
 */
function flowtitude_save_bricks_components($active_components) {
    try {
        if (!is_array($active_components)) {
            return new WP_Error('invalid_data', 'Los datos proporcionados no son válidos', ['status' => 400]);
        }
        
        // Filtrar para asegurarse de que solo se guarden rutas válidas
        $bricks_dir = flowtitude_get_custom_dir('bricks');
        $valid_components = [];
        
        foreach ($active_components as $component) {
            // Verificar que el componente existe
            $component_path = $bricks_dir . '/' . $component;
            if (file_exists($component_path)) {
                $valid_components[] = $component;
            }
        }
        
        update_option('flowtitude_active_bricks', $valid_components);
        return true;
    } catch (Exception $e) {
        error_log('Flowtitude: Error al guardar componentes de Bricks: ' . $e->getMessage());
        return new WP_Error('error', 'Error al guardar componentes', ['status' => 500]);
    }
}
