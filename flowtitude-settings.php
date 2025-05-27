<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registra el menú de administración de Flowtitude.
 *
 * @return void
 */
function flowtitude_add_admin_menu() {
    $hook = add_menu_page(
        'Flowtitude Settings',
        'Flowtitude',
        'manage_options',
        'flowtitude-settings',
        'flowtitude_settings_page',
        'dashicons-admin-customizer',
        30
    );
    if (function_exists('flowtitude_debug_log')) {
        if ($hook) {
            flowtitude_debug_log('Menú de administración de Flowtitude añadido correctamente.', 'success');
        } else {
            flowtitude_debug_log('No se pudo añadir el menú de administración de Flowtitude.', 'warning');
        }
    }
}
add_action('admin_menu', 'flowtitude_add_admin_menu');

/**
 * Renderiza la página de configuración de Flowtitude.
 * Valida permisos y añade logs de depuración.
 *
 * @return void
 */
function flowtitude_settings_page() {
    if (!current_user_can('manage_options')) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Intento de acceso no autorizado a la página de configuración de Flowtitude.', 'warning');
        }
        echo '<div class="notice notice-error"><p>No tienes permisos para acceder a esta página.</p></div>';
        return;
    }
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Página de configuración de Flowtitude cargada para el usuario: ' . wp_get_current_user()->user_login, 'info');
    }
    ?>
    <div class="wrap">
        <div id="flowtitude-settings"></div>
    </div>
    <?php
}

/**
 * Carga los scripts y estilos necesarios para la página de configuración de Flowtitude.
 * Añade logs de depuración y valida la existencia de archivos JS.
 *
 * @param string $hook
 * @return void
 */
function flowtitude_enqueue_admin_scripts($hook) {
    if ('toplevel_page_flowtitude-settings' !== $hook) {
        return;
    }
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Preparando scripts para la página de configuración de Flowtitude.', 'info');
    }
    // Vue.js y Vue Router
    wp_enqueue_script('vue', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), '3.0.0', true);
    wp_enqueue_script('vue-router', 'https://unpkg.com/vue-router@4.0.15/dist/vue-router.global.js', array('vue'), '4.0.15', true);

    // Helper para validar existencia de archivos antes de versionar
    function flowtitude_enqueue_script_with_check($handle, $src, $deps, $path, $in_footer = true) {
        if (file_exists($path)) {
            wp_enqueue_script($handle, $src, $deps, filemtime($path), $in_footer);
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Script encolado: ' . $handle . ' [' . $src . ']', 'success');
            }
        } else {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('No se encontró el archivo JS: ' . $path, 'warning');
            }
        }
    }
    // Script principal de la aplicación
    flowtitude_enqueue_script_with_check(
        'flowtitude-admin',
        get_template_directory_uri() . '/admin-panel/js/admin-main.js',
        array('vue', 'vue-router'),
        get_template_directory() . '/admin-panel/js/admin-main.js'
    );
    // Componentes
    flowtitude_enqueue_script_with_check(
        'flowtitude-color-panel',
        get_template_directory_uri() . '/admin-panel/js/components/ColorPanel.js',
        array('flowtitude-admin'),
        get_template_directory() . '/admin-panel/js/components/ColorPanel.js'
    );
    flowtitude_enqueue_script_with_check(
        'flowtitude-typography-panel',
        get_template_directory_uri() . '/admin-panel/js/components/TypographyPanel.js',
        array('flowtitude-admin'),
        get_template_directory() . '/admin-panel/js/components/TypographyPanel.js'
    );

    wp_enqueue_script(
        'flowtitude-spacing-panel',
        get_template_directory_uri() . '/admin-panel/js/components/SpacingPanel.js',
        array('flowtitude-admin'),
        filemtime(get_template_directory() . '/admin-panel/js/components/SpacingPanel.js'),
        true
    );

    wp_enqueue_script(
        'flowtitude-layout-panel',
        get_template_directory_uri() . '/admin-panel/js/components/LayoutPanel.js',
        array('flowtitude-admin'),
        filemtime(get_template_directory() . '/admin-panel/js/components/LayoutPanel.js'),
        true
    );

    // Vistas
    wp_enqueue_script(
        'flowtitude-settings-view',
        get_template_directory_uri() . '/admin-panel/js/views/flowtitude-settings.js',
        array('flowtitude-admin'),
        filemtime(get_template_directory() . '/admin-panel/js/views/flowtitude-settings.js'),
        true
    );

    wp_enqueue_script(
        'flowtitude-design-settings',
        get_template_directory_uri() . '/admin-panel/js/views/DesignSettings.js',
        array('flowtitude-admin', 'flowtitude-settings-view'),
        filemtime(get_template_directory() . '/admin-panel/js/views/DesignSettings.js'),
        true
    );

    // Estilos
    wp_enqueue_style(
        'flowtitude-admin-panel',
        get_template_directory_uri() . '/admin-panel/css/admin-panel.css',
        array(),
        filemtime(get_template_directory() . '/admin-panel/css/admin-panel.css')
    );
}

add_action('admin_enqueue_scripts', 'flowtitude_enqueue_admin_scripts'); 