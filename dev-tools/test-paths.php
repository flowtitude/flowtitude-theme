<?php
/**
 * Script de prueba para el sistema de rutas dinámicas de Flowtitude
 * 
 * Este script verifica que todas las rutas se generen correctamente
 * y que no haya rutas hardcodeadas en el sistema.
 */

// Simular entorno de WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/../../../');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__DIR__) . '/../../../wp-content');
}

if (!defined('WPMU_PLUGIN_DIR')) {
    define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');
}

// Función para simular get_template_directory
if (!function_exists('get_template_directory')) {
    function get_template_directory() {
        return dirname(__DIR__);
    }
}

// Función para simular get_template_directory_uri
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() {
        return 'http://localhost/wp-content/themes/flowtitude-theme-v2';
    }
}

// Función para simular content_url
if (!function_exists('content_url')) {
    function content_url($path = '') {
        return 'http://localhost/wp-content' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// Función para simular admin_url
if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return 'http://localhost/wp-admin' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// Función para simular includes_url
if (!function_exists('includes_url')) {
    function includes_url($path = '') {
        return 'http://localhost/wp-includes' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// Función para simular wp_upload_dir
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => WP_CONTENT_DIR . '/uploads',
            'baseurl' => content_url('uploads')
        ];
    }
}

// Función para simular flowtitude_debug_log
if (!function_exists('flowtitude_debug_log')) {
    function flowtitude_debug_log($message, $level = 'info', $module = 'test') {
        echo "[$level][$module] $message\n";
    }
}

// Cargar el sistema de rutas
require_once dirname(__DIR__) . '/inc/settings/paths-config.php';

echo "=== PRUEBA DEL SISTEMA DE RUTAS DINÁMICAS ===\n\n";

// Obtener la instancia del sistema de rutas
$paths_config = Flowtitude_Paths_Config::get_instance();

// Probar rutas básicas
echo "1. RUTAS BÁSICAS:\n";
echo "   - Tema root: " . flowtitude_get_path('theme.root') . "\n";
echo "   - Tema root URL: " . flowtitude_get_path('theme.root', true) . "\n";
echo "   - Uploads base: " . flowtitude_get_path('uploads.base') . "\n";
echo "   - Uploads base URL: " . flowtitude_get_path('uploads.base', true) . "\n";
echo "   - WP content: " . flowtitude_get_path('wp.content') . "\n";
echo "   - WP content URL: " . flowtitude_get_path('wp.content', true) . "\n\n";

// Probar rutas de archivos específicos
echo "2. RUTAS DE ARCHIVOS:\n";
echo "   - Tailwind CSS: " . flowtitude_get_path('files.tailwind_css') . "\n";
echo "   - Tailwind CSS URL: " . flowtitude_get_path('files.tailwind_css', true) . "\n";
echo "   - Flowtitude CSS: " . flowtitude_get_path('files.flowtitude_css') . "\n";
echo "   - Flowtitude CSS URL: " . flowtitude_get_path('files.flowtitude_css', true) . "\n";
echo "   - Tailwind config: " . flowtitude_get_path('files.tailwind_config') . "\n";
echo "   - Dashboard fixes: " . flowtitude_get_path('files.dashboard_fixes') . "\n";
echo "   - Dashboard fixes URL: " . flowtitude_get_path('files.dashboard_fixes', true) . "\n\n";

// Probar rutas de Bricks
echo "3. RUTAS DE BRICKS:\n";
echo "   - Bricks root: " . flowtitude_get_path('bricks.root') . "\n";
echo "   - Bricks root URL: " . flowtitude_get_path('bricks.root', true) . "\n";
echo "   - Bricks parser: " . flowtitude_get_path('bricks.parser') . "\n";
echo "   - Bricks parser existe: " . (flowtitude_path_exists('bricks.parser') ? 'SÍ' : 'NO') . "\n\n";

// Probar rutas de uploads
echo "4. RUTAS DE UPLOADS:\n";
echo "   - Flowtitude uploads: " . flowtitude_get_path('uploads.flowtitude') . "\n";
echo "   - Flowtitude uploads URL: " . flowtitude_get_path('uploads.flowtitude', true) . "\n";
echo "   - Windpress: " . flowtitude_get_path('uploads.windpress') . "\n";
echo "   - Windpress URL: " . flowtitude_get_path('uploads.windpress', true) . "\n";
echo "   - Windpress cache: " . flowtitude_get_path('uploads.windpress_cache') . "\n";
echo "   - Windpress cache URL: " . flowtitude_get_path('uploads.windpress_cache', true) . "\n\n";

// Probar rutas del tema
echo "5. RUTAS DEL TEMA:\n";
echo "   - Snippets: " . flowtitude_get_path('theme.snippets') . "\n";
echo "   - Snippets URL: " . flowtitude_get_path('theme.snippets', true) . "\n";
echo "   - Admin panel: " . flowtitude_get_path('theme.admin_panel') . "\n";
echo "   - Admin panel URL: " . flowtitude_get_path('theme.admin_panel', true) . "\n";
echo "   - Assets: " . flowtitude_get_path('theme.assets') . "\n";
echo "   - Assets URL: " . flowtitude_get_path('theme.assets', true) . "\n";
echo "   - CSS: " . flowtitude_get_path('theme.css') . "\n";
echo "   - CSS URL: " . flowtitude_get_path('theme.css', true) . "\n\n";

// Probar verificación de existencia
echo "6. VERIFICACIÓN DE EXISTENCIA:\n";
echo "   - Tema root existe: " . (flowtitude_path_exists('theme.root') ? 'SÍ' : 'NO') . "\n";
echo "   - Snippets existe: " . (flowtitude_path_exists('theme.snippets') ? 'SÍ' : 'NO') . "\n";
echo "   - Admin panel existe: " . (flowtitude_path_exists('theme.admin_panel') ? 'SÍ' : 'NO') . "\n";
echo "   - Tailwind CSS existe: " . (flowtitude_path_exists('files.tailwind_css') ? 'SÍ' : 'NO') . "\n";
echo "   - Flowtitude CSS existe: " . (flowtitude_path_exists('files.flowtitude_css') ? 'SÍ' : 'NO') . "\n\n";

// Probar creación de directorios
echo "7. CREACIÓN DE DIRECTORIOS:\n";
echo "   - Crear directorio de uploads flowtitude: " . (flowtitude_ensure_directory('uploads.flowtitude') ? 'OK' : 'ERROR') . "\n";
echo "   - Crear directorio de windpress: " . (flowtitude_ensure_directory('uploads.windpress') ? 'OK' : 'ERROR') . "\n";
echo "   - Crear directorio de windpress cache: " . (flowtitude_ensure_directory('uploads.windpress_cache') ? 'OK' : 'ERROR') . "\n";
echo "   - Crear directorio de windpress data: " . (flowtitude_ensure_directory('uploads.windpress_data') ? 'OK' : 'ERROR') . "\n";
echo "   - Crear directorio de windpress theme: " . (flowtitude_ensure_directory('uploads.windpress_theme') ? 'OK' : 'ERROR') . "\n\n";

// Mostrar todas las rutas configuradas
echo "8. TODAS LAS RUTAS CONFIGURADAS:\n";
$all_paths = $paths_config->get_all_paths();
foreach ($all_paths as $category => $paths) {
    echo "   [$category]:\n";
    foreach ($paths as $key => $path) {
        if (is_array($path)) {
            foreach ($path as $subkey => $subpath) {
                echo "     - $key.$subkey: $subpath\n";
            }
        } else {
            echo "     - $key: $path\n";
        }
    }
    echo "\n";
}

echo "=== PRUEBA COMPLETADA ===\n"; 