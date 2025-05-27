<?php
if (!defined('ABSPATH')) exit;

/**
 * Handler para mostrar la demo de Flowtitude solo a usuarios autorizados.
 * Añade logs de depuración, robustez y evita exponer rutas internas en errores.
 */
add_action('template_redirect', function() {
    if (!isset($_GET['flowtitude_demo'])) return;
    if (!current_user_can('edit_posts')) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Intento de acceso no autorizado al demo handler.', 'warning');
        }
        wp_die('No tienes permisos para ver esta página');
    }
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Demo handler iniciado por usuario: ' . wp_get_current_user()->user_login, 'info');
    }
    // Preparar las rutas
    $theme_dir = dirname(dirname(__DIR__));
    $demo_file = $theme_dir . '/admin-panel/previews/demo.html';
    $upload_dir = wp_upload_dir();
    $base_css_dir = $upload_dir['basedir'] . '/windpress/data';
    if (!file_exists($demo_file)) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Archivo de demo no encontrado: ' . $demo_file, 'warning');
        }
        wp_die('No se pudo encontrar el archivo de demo. Contacta al administrador.');
    }
    // Obtener el contenido del demo
    $content = file_get_contents($demo_file);
    if ($content === false) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Error al leer el archivo de demo: ' . $demo_file, 'error');
        }
        wp_die('No se pudo leer el archivo de demo. Contacta al administrador.');
    }
    // Iniciar el buffer para capturar la salida
    ob_start();
    // 1. Cargar Tailwind Play CDN primero
    echo '<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>';
    // 2. Agregar el CSS personalizado
    echo '<style type="text/tailwindcss">';
    // Cargar el tema principal sin modificaciones
    $css_path = $base_css_dir . '/theme/flowtitude.css';
    if (file_exists($css_path)) {
        echo file_get_contents($css_path);
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('CSS de demo cargado: ' . $css_path, 'info');
        }
    } else {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('No se encontró el CSS de demo: ' . $css_path, 'warning');
        }
    }
    echo '</style>';
    // Obtener todos los scripts y estilos generados
    $head_content = ob_get_clean();
    // Asegurarnos de que los scripts se inserten después de cualquier otro script en el head
    $content = str_replace('</head>', $head_content . '</head>', $content);
    // Enviar el contenido
    header('Content-Type: text/html; charset=UTF-8');
    echo $content;
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Demo entregada correctamente al usuario: ' . wp_get_current_user()->user_login, 'success');
    }
    exit;
}); 