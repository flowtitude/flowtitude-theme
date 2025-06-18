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
add_action('wp_dashboard_setup', 'flowtitude_maybe_remove_dashboard_widgets');

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
        // Renderiza el shortcode de Bricks
        remove_action('wp_head', 'wp_admin_bar_header');
        ob_start();
        do_action('wp_head');
        $frontend_head = ob_get_clean();
        ob_start();
        wp_print_styles();
        wp_print_scripts();
        $extra_resources = ob_get_clean();
        if (false === strpos($frontend_head, "bricks-frontend-inline-inline-css")) {
            ob_start();
            wp_print_styles('bricks-frontend-inline-inline-css');
            $bricks_inline_css = ob_get_clean();
            $frontend_head .= $bricks_inline_css;
            echo '
            <style>
                .postbox-container { width: 100% !important; }
                .postbox-header, #screen-meta-links { display: none; }
                .inside { margin: 0 !important; padding: 0 !important; }
                #wpcontent { padding-left: 0 !important; }
                .wrap { margin: 0 !important; width: 100% !important; display: flex !important; flex-direction: column; overflow-x: hidden; }
                #dashboard-widgets { padding: 0 !important; }
                .wrap h1:first-of-type { display: none; }
                .postbox { border: none !important; }
            </style>
            ';
        }
        ob_start();
        do_action('wp_footer');
        $frontend_footer = ob_get_clean();
        echo $frontend_head . $extra_resources;
        echo do_shortcode('[bricks_template id="' . $template_id . '"]');
        echo $frontend_footer;
    }
}

function flowtitude_enqueue_bricks_assets_admin() {
    $settings = get_option('flowtitude_settings', []);
    $template_id = isset($settings['custom_dashboard_template']) ? intval($settings['custom_dashboard_template']) : 0;
    if (!$template_id) return;
    // Solo en el dashboard principal
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
    }
}
add_action('admin_enqueue_scripts', 'flowtitude_enqueue_bricks_assets_admin');

/**
 * Encola los estilos de Bricks y del admin de WordPress en capas CSS usando @layer, solo en el dashboard admin.
 */
function flowtitude_enqueue_layered_admin_css() {
    $settings = get_option('flowtitude_settings', []);
    $template_id = isset($settings['custom_dashboard_template']) ? intval($settings['custom_dashboard_template']) : 0;
    if (!$template_id) return;
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'dashboard') return;
    flowtitude_enqueue_layered_css('dashboard');
}
add_action('admin_enqueue_scripts', 'flowtitude_enqueue_layered_admin_css', 998);

add_action('admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        $tailwind_url = content_url('uploads/windpress/cache/tailwind.css');
        wp_enqueue_style('flowtitude-tailwind', $tailwind_url, [], null);
    }
}, 1);

add_action('admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        global $wp_styles;
        if (!isset($wp_styles) || !is_object($wp_styles)) return;

        $layers_order = '@layer wordpress-layer, plugins-layer, bricks, theme, base, layouts, components, utilities, custom;';
        $first = true;

        foreach ($wp_styles->registered as $key => $style) {
            // Solo afecta a los estilos de admin-bar y admin-menu
            if (
                !is_string($style->src) || empty($style->src) ||
                (
                    strpos($style->handle, 'admin-bar') === false &&
                    strpos($style->handle, 'admin-menu') === false &&
                    strpos($style->src, 'admin-bar.css') === false &&
                    strpos($style->src, 'admin-menu.css') === false
                )
            ) {
                continue;
            }
            $code = $first ? $layers_order . "\n" : '';
            $first = false;
            $code .= '@import url("' . esc_url($style->src) . '") layer(custom);';
            $wp_styles->add_data($style->handle, 'after', [$code]);
            $wp_styles->registered[$key]->src = '';
        }
    }
}, 100); 