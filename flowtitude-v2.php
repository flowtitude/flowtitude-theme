<?php
/**
 * Carga e inicialización del tema Flowtitude v2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constantes globales del tema
if (!defined('FLOWTITUDE_DIR')) define('FLOWTITUDE_DIR', get_stylesheet_directory());
if (!defined('FLOWTITUDE_URL')) define('FLOWTITUDE_URL', get_stylesheet_directory_uri());
if (!defined('FLOWTITUDE_VERSION')) define('FLOWTITUDE_VERSION', '2.0.0');
if (!defined('FLOWTITUDE_MIN_WP_VERSION')) define('FLOWTITUDE_MIN_WP_VERSION', '6.0');
if (!defined('FLOWTITUDE_MIN_PHP_VERSION')) define('FLOWTITUDE_MIN_PHP_VERSION', '8.0');

/**
 * Verifica los requisitos mínimos de PHP y WordPress para el tema.
 * Añade logs de depuración y muestra avisos en el admin si no se cumplen.
 *
 * @return bool
 */
function flowtitude_check_requirements() {
    global $wp_version;
    $errors = [];
    if (version_compare(PHP_VERSION, FLOWTITUDE_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            'Flowtitude requiere PHP %s o superior. Tu servidor tiene PHP %s.',
            FLOWTITUDE_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }
    if (version_compare($wp_version, FLOWTITUDE_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            'Flowtitude requiere WordPress %s o superior. Tu sitio tiene WordPress %s.',
            FLOWTITUDE_MIN_WP_VERSION,
            $wp_version
        );
    }
    if (!empty($errors)) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Requisitos mínimos NO cumplidos: ' . implode(' | ', $errors), 'error');
        }
        add_action('admin_notices', function() use ($errors) {
            echo '<div class="error"><p>';
            echo '<strong>Flowtitude:</strong> ';
            echo implode('<br>', $errors);
            echo '</p></div>';
        });
        return false;
    }
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Requisitos mínimos cumplidos. PHP: ' . PHP_VERSION . ', WP: ' . $wp_version, 'success');
    }
    return true;
}

// Solo cargar el tema si cumple los requisitos
if (flowtitude_check_requirements()) {
    // ========== CARGA DEL CORE ==========
    // Primero cargamos las funciones básicas y utilidades
    $core_files = [
        FLOWTITUDE_DIR . '/inc/core/init.php',
        FLOWTITUDE_DIR . '/inc/core/loader.php'
    ];
    foreach ($core_files as $file) {
        if (file_exists($file)) {
            require_once $file;
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Archivo core cargado: ' . $file, 'success');
            }
        } else {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('No se encontró el archivo core: ' . $file, 'warning');
            }
        }
    }

    // ========== ACTIVACIÓN DEL TEMA ==========
    /**
     * Función de activación del tema Flowtitude.
     * Crea directorios necesarios y añade logs de depuración.
     *
     * @return void
     */
    function flowtitude_theme_activation() {
        // Crear directorios necesarios
        if (function_exists('flowtitude_get_custom_dir')) {
            $snippets = flowtitude_get_custom_dir('snippets');
            $bricks   = flowtitude_get_custom_dir('bricks');

            @chmod($snippets, 0755);
            @chmod($bricks,   0755);
        }

        // Copiar el mu-plugin de configuración avanzada
        $src_mu = get_stylesheet_directory() . '/inc/mu-plugins/flowtitude-config.php';
        $dst_dir = dirname(WP_CONTENT_DIR) . '/wp-content/mu-plugins';
        $dst_mu = $dst_dir . '/flowtitude-config.php';
        if (!file_exists($dst_dir)) {
            @mkdir($dst_dir, 0755, true);
        }
        if (file_exists($src_mu)) {
            if (!@copy($src_mu, $dst_mu)) {
                add_action('admin_notices', function() use ($dst_mu) {
                    echo '<div class="notice notice-error"><p><strong>Flowtitude:</strong> No se pudo copiar el mu-plugin de configuración a <code>' . esc_html($dst_mu) . '</code>. Por favor, copia el archivo manualmente.</p></div>';
                });
            }
        } else {
            add_action('admin_notices', function() use ($src_mu) {
                echo '<div class="notice notice-error"><p><strong>Flowtitude:</strong> No se encontró el archivo fuente del mu-plugin en <code>' . esc_html($src_mu) . '</code>.</p></div>';
            });
        }

        // Cargar configuración por defecto si no existe
        if (false === get_option('flowtitude_settings')) {
            add_option('flowtitude_settings', flowtitude_get_settings_defaults());
        }

        // Limpiar reglas de reescritura
        flush_rewrite_rules();
    }
    add_action('after_switch_theme', 'flowtitude_theme_activation');

    // ========== CARGA DEL PANEL ADMIN ==========
    $admin_panel = FLOWTITUDE_DIR . '/inc/admin/menu.php';
    if (file_exists($admin_panel)) {
        require_once $admin_panel;
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Panel de administración cargado: ' . $admin_panel, 'success');
        }
    } else {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('No se encontró el panel de administración: ' . $admin_panel, 'warning');
        }
    }

    /**
     * Encola los estilos principales del tema Flowtitude.
     * Añade logs de depuración.
     *
     * @return void
     */
    function flowtitude_enqueue_assets() {
        // Solo encolar los estilos principales en el frontend.
        if (!is_admin()) {
            wp_enqueue_style('flowtitude-style', get_stylesheet_uri(), [], FLOWTITUDE_VERSION);
        }
    }
    add_action('wp_enqueue_scripts', 'flowtitude_enqueue_assets');
}

// Puedes seguir añadiendo módulos con esta estructura:
