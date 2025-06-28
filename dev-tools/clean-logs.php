<?php
/**
 * Script para limpiar logs problemáticos del tema Flowtitude
 * 
 * Este script reemplaza todos los error_log con el nuevo sistema de logging
 * para evitar saturar el log de WordPress.
 */

if (!defined('ABSPATH')) {
    // Si se ejecuta desde línea de comandos
    if (php_sapi_name() !== 'cli') {
        die('Este script debe ejecutarse desde línea de comandos o desde WordPress');
    }
    
    // Simular WordPress para ejecución CLI
    define('ABSPATH', dirname(__FILE__) . '/../../');
    require_once ABSPATH . 'wp-config.php';
}

// Función para reemplazar error_log con flowtitude_debug_log
function replace_error_logs($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    // Patrones de reemplazo
    $replacements = [
        // error_log('Flowtitude: mensaje') -> flowtitude_debug_log('mensaje', 'info', 'context')
        '/error_log\(\s*[\'"]Flowtitude:\s*([^\'"]+)[\'"]\s*\);/' => 'flowtitude_debug_log(\'$1\', \'info\', \'auto\');',
        
        // error_log('mensaje') -> flowtitude_debug_log('mensaje', 'debug', 'auto')
        '/error_log\(\s*[\'"]([^\'"]+)[\'"]\s*\);/' => 'flowtitude_debug_log(\'$1\', \'debug\', \'auto\');',
        
        // error_log con print_r -> flowtitude_debug_log con print_r
        '/error_log\(\s*([^)]+print_r[^)]+)\);/' => 'flowtitude_debug_log($1, \'debug\', \'auto\');',
    ];
    
    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Solo escribir si hubo cambios
    if ($content !== $original_content) {
        file_put_contents($file_path, $content);
        return true;
    }
    
    return false;
}

// Archivos a procesar
$files_to_process = [
    'inc/features/custom-dashboard.php',
    'inc/core/loader.php',
    'inc/settings/api-endpoints.php',
    'inc/settings/snippet-folders-endpoint.php',
    'inc/mu-plugins/flowtitude-config.php',
];

$theme_dir = get_stylesheet_directory();
$processed = 0;
$modified = 0;

echo "Iniciando limpieza de logs en el tema Flowtitude...\n";

foreach ($files_to_process as $file) {
    $full_path = $theme_dir . '/' . $file;
    
    if (file_exists($full_path)) {
        $processed++;
        echo "Procesando: $file\n";
        
        if (replace_error_logs($full_path)) {
            $modified++;
            echo "  ✓ Modificado\n";
        } else {
            echo "  - Sin cambios\n";
        }
    } else {
        echo "  ✗ No encontrado: $file\n";
    }
}

echo "\nResumen:\n";
echo "- Archivos procesados: $processed\n";
echo "- Archivos modificados: $modified\n";
echo "\nPara activar el logging, añade al wp-config.php:\n";
echo "define('FLOWTITUDE_LOG', true);\n";
echo "define('FLOWTITUDE_LOG_LEVEL', 'info'); // error, warning, info, debug\n";
echo "define('FLOWTITUDE_LOG_TO_FILE', true); // true para archivo separado\n"; 