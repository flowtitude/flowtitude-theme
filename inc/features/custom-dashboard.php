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

function flowtitude_display_custom_dashboard_metabox() {
    $settings = get_option('flowtitude_settings', []);
    $template_id = isset($settings['custom_dashboard_template']) ? intval($settings['custom_dashboard_template']) : 0;
    if ($template_id) {
        ob_start();
        do_action('wp_head');
        $frontend_head = ob_get_clean();
        ob_start();
        wp_print_styles();
        wp_print_scripts();
        $extra_resources = ob_get_clean();
        ob_start();
        do_action('wp_footer');
        $frontend_footer = ob_get_clean();
        echo $frontend_head . $extra_resources;
        echo do_shortcode('[bricks_template id="' . $template_id . '"]');
        echo $frontend_footer;
    }
}

function flowtitude_enqueue_bricks_assets_admin() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        // Verificar si estamos usando la plantilla de Bricks
        $settings = get_option('flowtitude_settings', []);
        $template_id = !empty($settings['custom_dashboard_template']) ? (int)$settings['custom_dashboard_template'] : 0;

        if ($template_id) {
            // Listar todos los handles de estilos encolados
            global $wp_styles;
            error_log('Estilos encolados: ' . implode(', ', $wp_styles->queue));
        }

        // Encolar Tailwind desde uploads
        $tailwind_url = content_url('uploads/windpress/cache/tailwind.css');
        wp_enqueue_style('flowtitude-tailwind', $tailwind_url, [], null);

        // Forzar estilos y scripts de Bricks
        if (wp_style_is('bricks-frontend', 'registered')) {
            wp_enqueue_style('bricks-frontend');
        }
        if (wp_script_is('bricks-frontend', 'registered')) {
            wp_enqueue_script('bricks-frontend');
        }
        if (wp_script_is('bricks-frontend-inline', 'registered')) {
            wp_enqueue_script('bricks-frontend-inline');
        }

        // Cargar el archivo de correcciones para la UI del admin.
        $fixes_file_path = get_stylesheet_directory() . '/admin-panel/css/dashboard-fixes.css';
        if (file_exists($fixes_file_path)) {
            wp_enqueue_style(
                'flowtitude-dashboard-fixes',
                get_stylesheet_directory_uri() . '/admin-panel/css/dashboard-fixes.css',
                ['flowtitude-tailwind', 'bricks-frontend'], // Asegurar que se cargue después
                filemtime($fixes_file_path)
            );
        }

        // Encolar admin-menu.min.css desde el directorio wp-admin
        wp_enqueue_style('admin-menu-styles', admin_url('css/admin-menu.min.css'), [], null);
    }
}
add_action('admin_enqueue_scripts', 'flowtitude_enqueue_bricks_assets_admin');

function remover_common_css_en_dashboard($hook_suffix) {
    // Solo aplicar en la pantalla principal del Escritorio (admin index.php)
    if ($hook_suffix === 'index.php') {
        $settings = get_option('flowtitude_settings', []);
        $template_id = !empty($settings['custom_dashboard_template']) ? (int)$settings['custom_dashboard_template'] : 0;

        if ($template_id) {
            wp_dequeue_style('common');       // Desencolar el estilo common.css
            wp_deregister_style('common');    // (Opcional) Desregistrarlo completamente
        }
    }
}
add_action('admin_enqueue_scripts', 'remover_common_css_en_dashboard', 100); 