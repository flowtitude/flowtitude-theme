<?php
if (!defined('ABSPATH')) exit;

/**
 * Cargar configuración de rutas dinámicas
 */
require_once FLOWTITUDE_DIR . '/inc/settings/paths-config.php';

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
        $path = flowtitude_get_path('theme.snippets') . '/' . $file;
        if (file_exists($path)) {
            if (function_exists('flowtitude_safe_include')) {
                flowtitude_safe_include($path, 'system-snippets');
            } else {
                include_once $path;
            }
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
            if (function_exists('flowtitude_safe_include')) {
                flowtitude_safe_include($real_path, 'custom-snippets');
            } else {
                include_once $real_path;
            }
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
    if (!class_exists('Bricks\Elements')) {
        flowtitude_debug_log('Bricks no está disponible, omitiendo carga de componentes', 'info', 'bricks');
        return;
    }
    
    $bricks_dir = flowtitude_get_custom_dir('bricks');
    $active_components = get_option('flowtitude_active_bricks', []);
    
    if (!is_array($active_components)) {
        $active_components = [];
        flowtitude_debug_log('No hay componentes activos de Bricks o el formato es incorrecto', 'warning', 'bricks');
        return;
    }
    
    flowtitude_debug_log('Cargando ' . count($active_components) . ' componentes activos de Bricks', 'info', 'bricks');
    
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
                flowtitude_debug_log('No se pudo encontrar el componente de Bricks: ' . $file, 'warning', 'bricks');
                continue;
            }
        }
        
        // Verificar que el archivo está dentro del directorio permitido
        $real_path = realpath($path);
        $real_bricks_dir = realpath($bricks_dir);
        
        if ($real_path && file_exists($real_path) && strpos($real_path, $real_bricks_dir) === 0) {
            flowtitude_debug_log('Cargando componente de Bricks: ' . $real_path, 'debug', 'bricks');
            if (function_exists('flowtitude_safe_include')) {
                flowtitude_safe_include($real_path, 'bricks-components');
            } else {
                include_once $real_path;
            }
        } else {
            flowtitude_debug_log('No se pudo cargar el componente de Bricks: ' . $file, 'warning', 'bricks');
            flowtitude_debug_log('Ruta real: ' . $real_path, 'debug', 'bricks');
            flowtitude_debug_log('Directorio base: ' . $real_bricks_dir, 'debug', 'bricks');
        }
    }
}

/**
 * Obtiene la lista de componentes de Bricks disponibles
 * 
 * @return array Array con los componentes organizados por tipo
 */
function flowtitude_get_bricks_components() {
    $bricks_dir = flowtitude_get_custom_dir('bricks');
    $components = [];
    
    if (!file_exists($bricks_dir)) {
        return $components;
    }
    
    $component_types = ['custom-elements', 'dynamic-tags', 'conditionals'];
    
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
            
            $components[$type][] = [
                'file' => $relative_path,
                'name' => $title,
                'description' => $description,
                'folder' => $folder,
                'type' => $type
            ];
        }
    }
    
    return $components;
}
