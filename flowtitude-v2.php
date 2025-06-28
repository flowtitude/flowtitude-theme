<?php
/**
 * Flowtitude v2 - Tema Hijo WordPress con Panel de Administración Avanzado
 * 
 * Este es el archivo principal del tema que maneja la inicialización,
 * verificación de requisitos, carga de módulos y activación del tema.
 * Incluye un sistema robusto de validación de archivos para máxima seguridad.
 * 
 * @package Flowtitude
 * @version 2.0.0
 * @author Ángel Julián
 * @since 2025-01-27
 * 
 * @link https://webyblog.es/docs/flowtitude
 */

if (!defined('ABSPATH')) {
    exit;
}

// ========== CONSTANTES GLOBALES DEL TEMA ==========
// Definir constantes esenciales para el funcionamiento del tema
if (!defined('FLOWTITUDE_DIR')) define('FLOWTITUDE_DIR', get_stylesheet_directory());
if (!defined('FLOWTITUDE_URL')) define('FLOWTITUDE_URL', get_stylesheet_directory_uri());
if (!defined('FLOWTITUDE_VERSION')) define('FLOWTITUDE_VERSION', '2.0.0');
if (!defined('FLOWTITUDE_MIN_WP_VERSION')) define('FLOWTITUDE_MIN_WP_VERSION', '6.0');
if (!defined('FLOWTITUDE_MIN_PHP_VERSION')) define('FLOWTITUDE_MIN_PHP_VERSION', '8.0');

/**
 * Verifica los requisitos mínimos de PHP y WordPress para el tema
 * 
 * Comprueba que el servidor cumple con los requisitos mínimos:
 * - PHP 8.0 o superior
 * - WordPress 6.0 o superior
 * 
 * Si no se cumplen los requisitos:
 * - Registra errores en el sistema de logging
 * - Muestra avisos visibles en el panel de administración
 * - Previene la carga completa del tema
 * 
 * @return bool True si se cumplen todos los requisitos, False en caso contrario
 */
function flowtitude_check_requirements() {
    global $wp_version;
    $errors = [];
    
    // Verificar versión de PHP
    if (version_compare(PHP_VERSION, FLOWTITUDE_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            'Flowtitude requiere PHP %s o superior. Tu servidor tiene PHP %s.',
            FLOWTITUDE_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }
    
    // Verificar versión de WordPress
    if (version_compare($wp_version, FLOWTITUDE_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            'Flowtitude requiere WordPress %s o superior. Tu sitio tiene WordPress %s.',
            FLOWTITUDE_MIN_WP_VERSION,
            $wp_version
        );
    }
    
    // Si hay errores, registrarlos y mostrar avisos
    if (!empty($errors)) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Requisitos mínimos NO cumplidos: ' . implode(' | ', $errors), 'error', 'init');
        }
        
        // Mostrar avisos en el panel de administración
        add_action('admin_notices', function() use ($errors) {
            echo '<div class="error"><p>';
            echo '<strong>Flowtitude:</strong> ';
            echo implode('<br>', $errors);
            echo '</p></div>';
        });
        return false;
    }
    
    // Registrar éxito si se cumplen los requisitos
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Requisitos mínimos cumplidos. PHP: ' . PHP_VERSION . ', WP: ' . $wp_version, 'success', 'init');
    }
    return true;
}

// ========== INICIALIZACIÓN CONDICIONAL DEL TEMA ==========
// Solo cargar el tema si cumple los requisitos mínimos
if (flowtitude_check_requirements()) {
    
    // ========== CARGA DEL CORE DEL TEMA ==========
    // Cargar archivos core esenciales con validación de seguridad
    $core_files = [
        FLOWTITUDE_DIR . '/inc/core/init.php',      // Inicialización general
        FLOWTITUDE_DIR . '/inc/core/loader.php'     // Sistema de carga de módulos
    ];
    
    foreach ($core_files as $file) {
        // Usar sistema de carga segura si está disponible
        if (function_exists('flowtitude_safe_require')) {
            if (flowtitude_safe_require($file, 'core')) {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Archivo core cargado: ' . $file, 'success', 'init');
                }
            } else {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('No se pudo cargar el archivo core: ' . $file, 'warning', 'init');
                }
            }
        } else {
            // Fallback sin validación (solo para compatibilidad)
            if (file_exists($file)) {
                require_once $file;
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Archivo core cargado (fallback): ' . $file, 'success', 'init');
                }
            } else {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('No se encontró el archivo core: ' . $file, 'warning', 'init');
                }
            }
        }
    }

    // ========== ACTIVACIÓN DEL TEMA ==========
    /**
     * Función de activación del tema Flowtitude
     * 
     * Se ejecuta cuando el tema se activa por primera vez. Realiza las
     * siguientes tareas:
     * - Crea directorios necesarios para snippets y bricks
     * - Copia el mu-plugin de configuración avanzada
     * - Inicializa configuraciones por defecto
     * - Limpia reglas de reescritura
     * 
     * @return void
     */
    function flowtitude_theme_activation() {
        // Crear y configurar directorios necesarios
        if (function_exists('flowtitude_get_custom_dir')) {
            $snippets = flowtitude_get_custom_dir('snippets');
            $bricks   = flowtitude_get_custom_dir('bricks');

            // Establecer permisos correctos
            @chmod($snippets, 0755);
            @chmod($bricks,   0755);
            
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Directorios de tema configurados: snippets=' . $snippets . ', bricks=' . $bricks, 'success', 'activation');
            }
        }

        // Copiar el mu-plugin de configuración avanzada
        $src_mu = get_stylesheet_directory() . '/inc/mu-plugins/flowtitude-config.php';
        $dst_dir = dirname(WP_CONTENT_DIR) . '/wp-content/mu-plugins';
        $dst_mu = $dst_dir . '/flowtitude-config.php';
        
        // Crear directorio mu-plugins si no existe
        if (!file_exists($dst_dir)) {
            @mkdir($dst_dir, 0755, true);
        }
        
        // Copiar archivo mu-plugin
        if (file_exists($src_mu)) {
            if (!@copy($src_mu, $dst_mu)) {
                add_action('admin_notices', function() use ($dst_mu) {
                    echo '<div class="notice notice-error"><p><strong>Flowtitude:</strong> No se pudo copiar el mu-plugin de configuración a <code>' . esc_html($dst_mu) . '</code>. Por favor, copia el archivo manualmente.</p></div>';
                });
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Error al copiar mu-plugin: ' . $dst_mu, 'error', 'activation');
                }
            } else {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Mu-plugin copiado correctamente: ' . $dst_mu, 'success', 'activation');
                }
            }
        } else {
            add_action('admin_notices', function() use ($src_mu) {
                echo '<div class="notice notice-error"><p><strong>Flowtitude:</strong> No se encontró el archivo fuente del mu-plugin en <code>' . esc_html($src_mu) . '</code>.</p></div>';
            });
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Archivo fuente del mu-plugin no encontrado: ' . $src_mu, 'error', 'activation');
            }
        }

        // Inicializar configuraciones por defecto si no existen
        if (false === get_option('flowtitude_settings')) {
            if (function_exists('flowtitude_get_settings_defaults')) {
                add_option('flowtitude_settings', flowtitude_get_settings_defaults());
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Configuraciones por defecto inicializadas', 'success', 'activation');
                }
            }
        }

        // Limpiar reglas de reescritura para asegurar que funcionen correctamente
        flush_rewrite_rules();
        
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Activación del tema Flowtitude completada', 'success', 'activation');
        }
    }
    add_action('after_switch_theme', 'flowtitude_theme_activation');

    // ========== CARGA DEL PANEL DE ADMINISTRACIÓN ==========
    // Cargar el panel de administración personalizado
    $admin_panel = FLOWTITUDE_DIR . '/inc/admin/menu.php';
    if (file_exists($admin_panel)) {
        if (function_exists('flowtitude_safe_require')) {
            if (flowtitude_safe_require($admin_panel, 'admin')) {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('Panel de administración cargado: ' . $admin_panel, 'success', 'init');
                }
            } else {
                if (function_exists('flowtitude_debug_log')) {
                    flowtitude_debug_log('No se pudo cargar el panel de administración: ' . $admin_panel, 'warning', 'init');
                }
            }
        } else {
            // Fallback sin validación
            require_once $admin_panel;
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Panel de administración cargado (fallback): ' . $admin_panel, 'success', 'init');
            }
        }
    } else {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('No se encontró el panel de administración: ' . $admin_panel, 'warning', 'init');
        }
    }

    // ========== CARGA DE RECURSOS DEL FRONTEND ==========
    /**
     * Encola los estilos principales del tema Flowtitude
     * 
     * Carga los estilos CSS del tema solo en el frontend (no en admin).
     * Los estilos se cargan con versionado para evitar problemas de caché.
     * 
     * @return void
     */
    function flowtitude_enqueue_assets() {
        // Solo encolar los estilos principales en el frontend
        if (!is_admin()) {
            wp_enqueue_style('flowtitude-style', get_stylesheet_uri(), [], FLOWTITUDE_VERSION);
            
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Estilos del tema encolados: ' . get_stylesheet_uri(), 'debug', 'assets');
            }
        }
    }
    add_action('wp_enqueue_scripts', 'flowtitude_enqueue_assets');
    
    // ========== FINALIZACIÓN DE LA INICIALIZACIÓN ==========
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Tema Flowtitude v' . FLOWTITUDE_VERSION . ' inicializado correctamente', 'success', 'init');
    }
    
} else {
    // Si no se cumplen los requisitos, registrar el problema
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Tema Flowtitude NO inicializado - requisitos no cumplidos', 'error', 'init');
    }
}

// ========== NOTAS PARA DESARROLLADORES ==========
// 
// Para añadir nuevos módulos al tema, sigue esta estructura:
// 
// 1. Crea el archivo en /inc/features/ o /inc/settings/
// 2. Usa flowtitude_safe_require() para cargarlo
// 3. Añade logging apropiado
// 4. Documenta la funcionalidad
// 
// Ejemplo:
// $feature_file = FLOWTITUDE_DIR . '/inc/features/mi-feature.php';
// if (flowtitude_safe_require($feature_file, 'features')) {
//     flowtitude_debug_log('Feature cargada: mi-feature', 'success', 'init');
// }
