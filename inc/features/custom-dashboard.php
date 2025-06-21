<?php
if (!defined('ABSPATH')) exit;

/**
 * Maneja la personalización del dashboard de WordPress
 * Permite reemplazar el contenido del dashboard con una plantilla de Bricks
 */

function flowtitude_maybe_remove_dashboard_widgets() {
    $settings = get_option('flowtitude_settings', []);
    if (!empty($settings['custom_dashboard_template'])) {
        remove_action('welcome_panel', 'wp_welcome_panel');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    }
}
add_action('wp_dashboard_setup', 'flowtitude_maybe_remove_dashboard_widgets', 999);

function flowtitude_maybe_add_dashboard_custom_metabox() {
    $settings = get_option('flowtitude_settings', []);
    $template_id = isset($settings['custom_dashboard_template']) ? intval($settings['custom_dashboard_template']) : 0;
    $title = __('Dashboard Personalizado', 'flowtitude');
    if ($template_id) {
        $template = get_post($template_id);
        if ($template && $template->post_type === 'bricks_template') {
            $title = $template->post_title;
        }
        add_meta_box(
            'flowtitude_custom_dashboard_metabox',
            $title,
            'flowtitude_display_custom_dashboard_metabox',
            'dashboard',
            'normal',
            'high'
        );
    }
}
add_action('wp_dashboard_setup', 'flowtitude_maybe_add_dashboard_custom_metabox');


// Start of Selection
function flowtitude_display_custom_dashboard_metabox() {
    $settings = get_option('flowtitude_settings', []);
    $template_id = isset($settings['custom_dashboard_template']) ? intval($settings['custom_dashboard_template']) : 0;
    if ($template_id) {
        echo do_shortcode('[bricks_template id="' . $template_id . '"]');
    }
}

function flowtitude_enqueue_bricks_assets_admin() {
    $settings = get_option('flowtitude_settings', []);
    // Condición reforzada: Asegurarse de que el template_id no solo existe, sino que es un post válido.
    $template_id = !empty($settings['custom_dashboard_template']) ? (int)$settings['custom_dashboard_template'] : 0;

    // Si no hay plantilla seleccionada, no hacer absolutamente nada.
    if (empty($template_id)) {
        return;
    }

    // Solo encolar si estamos en la pantalla del dashboard.
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        // Encolar Tailwind desde uploads
        $tailwind_url = content_url('uploads/windpress/cache/tailwind.css');
        wp_enqueue_style('flowtitude-tailwind', $tailwind_url, [], null);
        // Forzar estilos y scripts de Bricks si existen
        if (wp_style_is('bricks-frontend', 'registered')) {
            wp_enqueue_style('bricks-frontend');
        }
        if (wp_style_is('bricks-frontend-inline-inline-css', 'registered')) {
            wp_enqueue_style('bricks-frontend-inline-inline-css');
        }
        if (wp_script_is('bricks-frontend', 'registered')) {
            wp_enqueue_script('bricks-frontend');
        }
        if (wp_script_is('bricks-frontend-inline', 'registered')) {
            wp_enqueue_script('bricks-frontend-inline');
        }

        wp_enqueue_style('flowtitude-dashboard-style', get_stylesheet_directory_uri() . '/admin-panel/css/dashboard-fixes.css', [], '1.0');
    }
}
add_action('admin_enqueue_scripts', 'flowtitude_enqueue_bricks_assets_admin');

// Encolar nuestra hoja de estilos para correcciones visuales
wp_enqueue_style('flowtitude-dashboard-fixes', get_theme_file_uri('admin-panel/css/dashboard-fixes.css'), [], time());