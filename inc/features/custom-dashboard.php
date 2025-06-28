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

add_filter('bricks/dynamic_data/render_tag', 'procesar_etiquetas_dinamicas_dashboard', 10, 3);
add_filter('bricks/dynamic_data/render_content', 'procesar_etiquetas_dinamicas_dashboard', 20, 3);
add_filter('bricks/frontend/render_data', 'procesar_etiquetas_dinamicas_dashboard', 20, 2);

function procesar_etiquetas_dinamicas_dashboard($tag, $post = null, $context = 'text') {
    // Asegurarse de que el contexto sea el correcto
    if ($context !== 'dashboard') {
        return $tag;
    }

    // Lógica específica para el dashboard
    switch ($tag) {
        case 'mi_etiqueta_personalizada':
            return 'Valor personalizado para el dashboard';
        // Añadir más casos según sea necesario
        default:
            // Intentar procesar la etiqueta usando la lógica de Bricks
            if (function_exists('bricks_process_dynamic_tag')) {
                return bricks_process_dynamic_tag($tag, $post);
            }
            return $tag;
    }
}

function encolar_bricks_assets_dashboard() {
    // Asegúrate de que los scripts de Bricks estén disponibles
    if (!wp_script_is('bricks-builder', 'enqueued')) {
        wp_enqueue_script('bricks-builder', get_template_directory_uri() . '/path/to/bricks-builder.js', array('jquery'), null, true);
    }
    if (!wp_style_is('bricks-builder', 'enqueued')) {
        wp_enqueue_style('bricks-builder', get_template_directory_uri() . '/path/to/bricks-builder.css', array(), null);
    }
}
add_action('admin_enqueue_scripts', 'encolar_bricks_assets_dashboard');

// Filtros para procesar etiquetas dinámicas
add_filter('bricks/dynamic_data/render_content', 'procesar_contenido_etiquetas_dinamicas_dashboard', 20, 2);
add_filter('bricks/frontend/render_data', 'procesar_contenido_etiquetas_dinamicas_dashboard', 20, 2);

require_once get_template_directory() . '/../bricks/includes/integrations/dynamic-data/dynamic-data-parser.php';

use Bricks\Integrations\Dynamic_Data\Dynamic_Data_Parser;

function procesar_contenido_etiquetas_dinamicas_dashboard() {
    $args = func_get_args();
    $content = $args[0];
    $post = isset($args[1]) ? $args[1] : null;
    $context = isset($args[2]) ? $args[2] : 'text';

    // Verificar si el contenido contiene etiquetas dinámicas
    if (strpos($content, '{') === false) {
        return $content;
    }

    $parser = new Dynamic_Data_Parser();

    // Procesar etiquetas dinámicas
    $content = preg_replace_callback('/{([^}]+)}/', function($matches) use ($post, $parser) {
        $parsed_data = $parser->parse($matches[1]);
        $tag = $parsed_data['tag'];
        $args = $parsed_data['args'];

        // Manejar etiquetas de usuario
        
        if (strpos($tag, 'wp_user_') === 0) {
            $user = wp_get_current_user();
            switch ($tag) {
                case 'wp_user_nickname':
                    return $user->nickname;
                case 'wp_user_avatar':
                    return get_avatar_url($user->ID);
                // Añadir más casos según sea necesario
            }
        }
      

        // Sistema de procesamiento para ACF
        if (function_exists('get_field')) {
            $acf_value = get_field($tag, $post);
            if ($acf_value) {
                return $acf_value;
            }
        }

        // Preparar integración con Jet Engine
        // Aquí podríamos añadir lógica específica para Jet Engine

        // Si no se encuentra un reemplazo, devolver la etiqueta sin cambios
        return '{' . $tag . '}';
    }, $content);

    return $content;
}

class DashboardDynamicTagProcessor {
    public static function process_tags($content, $post = null) {
        // Verificar si el contenido contiene etiquetas dinámicas
        if (strpos($content, '{') === false) {
            return $content;
        }

        // Añadir registro de depuración
        error_log('Procesando contenido: ' . $content);

        // Procesar etiquetas dinámicas de Bricks
        if (function_exists('bricks_process_dynamic_tag')) {
            $content = preg_replace_callback('/{([^}]+)}/', function($matches) use ($post) {
                $processed_tag = bricks_process_dynamic_tag($matches[1], $post);
                error_log('Etiqueta procesada: ' . $matches[1] . ' -> ' . $processed_tag);
                return $processed_tag;
            }, $content);
        }

        // Procesar etiquetas de Jet Engine y ACF
        // Aquí puedes añadir lógica específica para Jet Engine y ACF si es necesario

        return $content;
    }
}

// Reemplazar el uso directo de la función por la clase
add_filter('bricks/dynamic_data/render_content', ['DashboardDynamicTagProcessor', 'process_tags'], 20, 2);
add_filter('bricks/frontend/render_data', ['DashboardDynamicTagProcessor', 'process_tags'], 20, 2); 