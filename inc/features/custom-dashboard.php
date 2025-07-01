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
        echo '<div class="ft-dashboard">';
        echo $frontend_head . $extra_resources;
        // DEBUG: Log antes de do_shortcode
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('DASHBOARD: Antes de do_shortcode para plantilla Bricks ID: ' . $template_id, 'debug', 'dashboard');
        }
        // Obtener el contenido de la plantilla Bricks
        $content = do_shortcode('[bricks_template id="' . $template_id . '"]');
        // DEBUG: Log del contenido original
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('DASHBOARD: Contenido original Bricks tras do_shortcode: ' . $content, 'debug', 'dashboard');
        }
        // Procesar etiquetas dinámicas usando el parser universal
        if (class_exists('\Flowtitude\Features\Flowtitude_Bricks_Dynamic_Resolver')) {
            $user = wp_get_current_user();
            $post = get_post($template_id);
            // DEBUG: Log de existencia de clase y estado del parser/providers
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('DASHBOARD: Flowtitude_Bricks_Dynamic_Resolver existe', 'debug', 'dashboard');
            }
            // DEBUG: Log de cada etiqueta encontrada y su valor procesado
            $processed_content = $content;
            if (preg_match_all('/{([^}]+)}/', $content, $matches)) {
                foreach ($matches[1] as $tag) {
                    $valor = \Flowtitude\Features\Flowtitude_Bricks_Dynamic_Resolver::parse('{' . $tag . '}', ['user' => $user, 'post' => $post]);
                    if (function_exists('flowtitude_debug_log')) {
                        flowtitude_debug_log('DASHBOARD: Etiqueta encontrada: {' . $tag . '} => ' . $valor, 'debug', 'dashboard');
                    }
                }
            } else {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('DASHBOARD: No se encontraron etiquetas dinámicas en el contenido.', 'debug', 'dashboard');
                }
            }
            // Procesar todo el contenido
            $processed_content = \Flowtitude\Features\Flowtitude_Bricks_Dynamic_Resolver::parse($content, ['user' => $user, 'post' => $post]);
            // DEBUG: Log del contenido procesado
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('DASHBOARD: Contenido procesado Bricks: ' . $processed_content, 'debug', 'dashboard');
            }
            $content = $processed_content;
        } else {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('DASHBOARD: La clase Flowtitude_Bricks_Dynamic_Resolver NO existe.', 'error', 'dashboard');
            }
        }
        echo $content;
        echo $frontend_footer;
        echo '</div>';
        // IMPORTANTE: Eliminar estos logs cuando se resuelva el problema de las etiquetas dinámicas.
    }
}

function flowtitude_enqueue_bricks_assets_admin() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        // Encolar Tailwind desde uploads
        $tailwind_url = content_url('uploads/windpress/cache/tailwind.css');
        wp_enqueue_style('flowtitude-tailwind', $tailwind_url, [], null);

        // Encolar solo el CSS del menú lateral del admin
        wp_enqueue_style('admin-menu');

        // Forzar estilos y scripts de Bricks si están registrados
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
                ['flowtitude-tailwind', 'admin-menu', 'bricks-frontend'],
                filemtime($fixes_file_path)
            );
        }
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

// Eliminar widgets/metaboxes nativos pero dejar el personalizado
add_action('wp_dashboard_setup', function() {
    global $wp_meta_boxes;
    // Elimina todos los metaboxes excepto el personalizado
    if (isset($wp_meta_boxes['dashboard'])) {
        foreach ($wp_meta_boxes['dashboard'] as $context => &$types) {
            foreach ($types as $type => &$boxes) {
                foreach ($boxes as $id => $box) {
                    if ($id !== 'flowtitude_custom_dashboard_metabox') {
                        unset($boxes[$id]);
                    }
                }
            }
        }
    }
}, 100);

// Quitar título y marco del metabox personalizado y hacerlo ancho completo
add_action('admin_head', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'dashboard') {
        echo '<style>
            #flowtitude_custom_dashboard_metabox .hndle, /* Título del metabox */
            #flowtitude_custom_dashboard_metabox .handle-actions, /* Acciones del metabox */
            #flowtitude_custom_dashboard_metabox .postbox-header {
                display: none !important;
            }
            #flowtitude_custom_dashboard_metabox.postbox {
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            #dashboard-widgets .postbox {
                margin-bottom: 0 !important;
            }
            #dashboard-widgets {
                padding: 0 !important;
            }
            /* Ancho completo */
            #flowtitude_custom_dashboard_metabox .inside {
                padding: 0 !important;
            }
            #flowtitude_custom_dashboard_metabox {
                width: 100% !important;
                max-width: 100vw !important;
            }
            /* Restaurar fuente original para el admin */
            body.wp-admin {
                font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif !important;
            }
        </style>';
    }
}, 100); 