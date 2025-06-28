<?php
/**
 * Mu-Plugin unificado de configuración para Flowtitude
 * 
 * Este archivo se carga automáticamente antes que cualquier plugin o tema
 * y maneja todas las configuraciones de Flowtitude de forma centralizada
 */

if (!defined('ABSPATH')) exit;

// ===== CONSTANTES DE LOGGING DEL TEMA =====
// Estas constantes controlan el logging específico del tema Flowtitude
// Se pueden modificar aquí para activar/desactivar logs específicos

// Logging general del tema
if (!defined('FLOWTITUDE_LOG')) define('FLOWTITUDE_LOG', false);

// Nivel de logging (error, warning, info, debug)
if (!defined('FLOWTITUDE_LOG_LEVEL')) define('FLOWTITUDE_LOG_LEVEL', 'error');

// Logging específico por módulos
if (!defined('FLOWTITUDE_LOG_DASHBOARD')) define('FLOWTITUDE_LOG_DASHBOARD', false);
if (!defined('FLOWTITUDE_LOG_ENDPOINTS')) define('FLOWTITUDE_LOG_ENDPOINTS', false);
if (!defined('FLOWTITUDE_LOG_SNIPPETS')) define('FLOWTITUDE_LOG_SNIPPETS', false);
if (!defined('FLOWTITUDE_LOG_SECURITY')) define('FLOWTITUDE_LOG_SECURITY', false);
if (!defined('FLOWTITUDE_LOG_BRICKS')) define('FLOWTITUDE_LOG_BRICKS', false);
if (!defined('FLOWTITUDE_LOG_HOOKS')) define('FLOWTITUDE_LOG_HOOKS', false);

// ===== CONFIGURACIÓN GLOBAL POR DEFECTO =====
// Estas constantes se pueden sobrescribir en wp-config.php si es necesario

// Configuración del tema
if (!defined('FLOWTITUDE_VERSION')) {
    define('FLOWTITUDE_VERSION', '2.0.0');
}

if (!defined('FLOWTITUDE_MIN_WP_VERSION')) {
    define('FLOWTITUDE_MIN_WP_VERSION', '6.0');
}

if (!defined('FLOWTITUDE_MIN_PHP_VERSION')) {
    define('FLOWTITUDE_MIN_PHP_VERSION', '8.0');
}

// Directorios por defecto
if (!defined('FLOWTITUDE_SNIPPETS_DIR')) {
    define('FLOWTITUDE_SNIPPETS_DIR', 'snippets');
}

if (!defined('FLOWTITUDE_BRICKS_DIR')) {
    define('FLOWTITUDE_BRICKS_DIR', 'bricks');
}

// ===== SISTEMA DE LOGGING UNIFICADO =====

/**
 * Función de logging unificada para Flowtitude
 * Maneja tanto logs del mu-plugin como del panel de administración
 */
function flowtitude_debug_log($message, $type = 'info', $context = 'flowtitude') {
    // Verificar si el logging general está activado
    if (!defined('FLOWTITUDE_LOG') || !FLOWTITUDE_LOG) {
        return;
    }
    
    // Verificar nivel de logging
    $levels = ['error' => 1, 'warning' => 2, 'info' => 3, 'debug' => 4];
    $current_level = defined('FLOWTITUDE_LOG_LEVEL') ? FLOWTITUDE_LOG_LEVEL : 'error';
    $message_level = isset($levels[$type]) ? $levels[$type] : 3;
    $max_level = isset($levels[$current_level]) ? $levels[$current_level] : 1;
    
    if ($message_level > $max_level) {
        return;
    }
    
    // Verificar logging específico por módulo
    $module_constants = [
        'dashboard' => 'FLOWTITUDE_LOG_DASHBOARD',
        'endpoints' => 'FLOWTITUDE_LOG_ENDPOINTS',
        'snippets' => 'FLOWTITUDE_LOG_SNIPPETS',
        'security' => 'FLOWTITUDE_LOG_SECURITY',
        'bricks' => 'FLOWTITUDE_LOG_BRICKS',
        'hooks' => 'FLOWTITUDE_LOG_HOOKS'
    ];
    
    // Si el contexto está en la lista de módulos, verificar su constante
    if (isset($module_constants[$context])) {
        $module_constant = $module_constants[$context];
        if (!defined($module_constant) || !constant($module_constant)) {
            return;
        }
    }
    
    // Obtener configuración de logging desde la base de datos (para compatibilidad)
    $security_settings = get_option('flowtitude_security_settings', []);
    
    // Verificar si el logging está activado desde el panel de administración
    $logging_enabled = !empty($security_settings['wp_debug']) || 
                      !empty($security_settings['wp_debug_log']) || 
                      !empty($security_settings['log_hooks']);
    
    // Si no hay configuración desde el panel, usar las constantes
    if (!$logging_enabled) {
        $logging_enabled = FLOWTITUDE_LOG;
    }
    
    if (!$logging_enabled) {
        return;
    }
    
    // Formatear el mensaje
    $formatted_message = '[Flowtitude ' . strtoupper($type) . '] [' . $context . '] ' . $message;
    
    // Determinar dónde escribir el log
    $log_path = !empty($security_settings['wp_debug_log_path']) ? 
                $security_settings['wp_debug_log_path'] : 
                WP_CONTENT_DIR . '/debug.log';
    
    // Escribir al log
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log($formatted_message);
    }
}

// ===== APLICACIÓN DE CONFIGURACIONES DE WORDPRESS =====

/**
 * Aplicar configuraciones de WordPress desde el panel de administración
 */
function flowtitude_apply_wp_configurations() {
    $security_settings = get_option('flowtitude_security_settings', []);
    
    // Configuraciones de debug
    if (!empty($security_settings['wp_debug'])) {
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
    }
    
    if (!empty($security_settings['wp_debug_display'])) {
        if (!defined('WP_DEBUG_DISPLAY')) {
            define('WP_DEBUG_DISPLAY', true);
        }
    }
    
    if (!empty($security_settings['wp_debug_log'])) {
        if (!defined('WP_DEBUG_LOG')) {
            define('WP_DEBUG_LOG', true);
        }
        if (!empty($security_settings['wp_debug_log_path'])) {
            if (!defined('WP_DEBUG_LOG_PATH')) {
                define('WP_DEBUG_LOG_PATH', $security_settings['wp_debug_log_path']);
            }
        }
    }
    
    if (!empty($security_settings['script_debug'])) {
        if (!defined('SCRIPT_DEBUG')) {
            define('SCRIPT_DEBUG', true);
        }
    }
    
    if (!empty($security_settings['savequeries'])) {
        if (!defined('SAVEQUERIES')) {
            define('SAVEQUERIES', true);
        }
    }
    
    if (!empty($security_settings['disable_wp_cron'])) {
        if (!defined('DISABLE_WP_CRON')) {
            define('DISABLE_WP_CRON', true);
        }
    }
    
    // Configuraciones de caché
    if (!empty($security_settings['wp_cache'])) {
        if (!defined('WP_CACHE')) {
            define('WP_CACHE', true);
        }
    }
    
    // Configuraciones de memoria
    if (!empty($security_settings['wp_memory_limit'])) {
        @ini_set('memory_limit', $security_settings['wp_memory_limit']);
    }
    
    if (!empty($security_settings['wp_max_memory_limit'])) {
        @ini_set('max_execution_time', 300);
    }
    
    // Solo log si hay configuraciones activas
    if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
        flowtitude_debug_log('Configuraciones de WordPress aplicadas desde panel de administración', 'info', 'config');
    }
}

// Aplicar configuraciones temprano
add_action('init', 'flowtitude_apply_wp_configurations', 1);

// ===== APLICACIÓN DE CONFIGURACIONES DE SEGURIDAD =====

/**
 * Aplicar configuraciones de seguridad
 */
function flowtitude_apply_security_settings() {
    $security_settings = get_option('flowtitude_security_settings', []);
    
    // Ocultar versión de WordPress
    if (!empty($security_settings['hide_wp_version'])) {
        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Versión de WordPress oculta', 'info', 'security');
        }
    }
    
    // Desactivar XML-RPC
    if (!empty($security_settings['disable_xmlrpc'])) {
        add_filter('xmlrpc_enabled', '__return_false');
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('XML-RPC desactivado', 'info', 'security');
        }
    }
    
    // Desactivar REST API para visitantes no logeados
    if (!empty($security_settings['disable_wp_api'])) {
        add_filter('rest_authentication_errors', function($result) {
            if (!empty($result)) {
                return $result;
            }
            if (!is_user_logged_in()) {
                return new WP_Error('rest_not_logged_in', 'No autorizado', ['status' => 401]);
            }
            return $result;
        });
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('REST API desactivada para visitantes', 'info', 'security');
        }
    }
    
    // Restricción por IP
    if (!empty($security_settings['allowed_ips'])) {
        $allowed_ips = array_map('trim', explode(',', $security_settings['allowed_ips']));
        $current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!in_array($current_ip, $allowed_ips) && is_admin()) {
            wp_die('Acceso denegado desde tu IP: ' . $current_ip);
        }
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Restricción por IP aplicada', 'info', 'security');
        }
    }
    
    // Desactivar transients
    if (!empty($security_settings['disable_transients'])) {
        add_filter('set_transient', '__return_false');
        add_filter('set_site_transient', '__return_false');
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Transients desactivados', 'info', 'security');
        }
    }
    
    // Desactivar Heartbeat API
    if (!empty($security_settings['disable_heartbeat'])) {
        wp_deregister_script('heartbeat');
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Heartbeat API desactivada', 'info', 'security');
        }
    }
    
    // Desactivar autosave
    if (!empty($security_settings['disable_autosave'])) {
        wp_deregister_script('autosave');
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Autosave desactivado', 'info', 'security');
        }
    }
    
    // Limitar revisiones
    if (isset($security_settings['revision_limit'])) {
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', intval($security_settings['revision_limit']));
        }
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Límite de revisiones aplicado: ' . $security_settings['revision_limit'], 'info', 'security');
        }
    }
}

add_action('init', 'flowtitude_apply_security_settings', 2);

// ===== SISTEMA DE BADGES/BANNERS =====

/**
 * Aplicar badges y banners para modos de desarrollo/migración
 */
function flowtitude_apply_development_badges() {
    $security_settings = get_option('flowtitude_security_settings', []);
    
    // Solo ejecutar si estamos en modo de desarrollo o migración
    if (!empty($security_settings['migration_mode']) || !empty($security_settings['development_mode'])) {
        // Solo log si el logging está activado
        if (!empty($security_settings['wp_debug']) || !empty($security_settings['wp_debug_log'])) {
            flowtitude_debug_log('Aplicando badges de desarrollo/migración', 'info', 'badges');
        }
        
        // Badge en admin bar
        add_action('admin_bar_menu', function($wp_admin_bar) use ($security_settings) {
            if (!is_user_logged_in() || !current_user_can('manage_options')) { 
                return; 
            }
            
            $mode = !empty($security_settings['migration_mode']) ? 'migration' : 'development';
            $color = $mode === 'migration' ? '#ff6b6b' : '#4ecdc4';
            $label = $mode === 'migration' ? 'MIGRATION' : 'DEV';
            
            $wp_admin_bar->add_node([
                'id' => 'flowtitude-badge',
                'title' => $label,
                'href' => '#',
                'meta' => [
                    'style' => "background-color: {$color} !important; color: white !important; font-weight: bold !important;"
                ]
            ]);
        }, 999);
        
        // Estilos para el badge
        add_action('admin_head', function() use ($security_settings) {
            ?>
            <style>
            #wp-admin-bar-flowtitude-badge > a {
                background-color: <?php echo !empty($security_settings['migration_mode']) ? '#ff6b6b' : '#4ecdc4'; ?> !important;
                color: white !important;
                font-weight: bold !important;
            }
            </style>
            <?php
        });
        
        // Banner en frontend
        add_action('wp_head', function() use ($security_settings) {
            if (!is_user_logged_in() || !current_user_can('manage_options')) { 
                return; 
            }
            
            $mode = !empty($security_settings['migration_mode']) ? 'migration' : 'development';
            $color = $mode === 'migration' ? '#ff6b6b' : '#4ecdc4';
            $label = $mode === 'migration' ? 'MIGRATION MODE' : 'DEVELOPMENT MODE';
            
            ?>
            <style>
            .flowtitude-banner {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background-color: <?php echo $color; ?>;
                color: white;
                text-align: center;
                padding: 5px;
                font-weight: bold;
                z-index: 999999;
                font-size: 12px;
            }
            body { padding-top: 30px !important; }
            </style>
            <div class="flowtitude-banner"><?php echo $label; ?></div>
            <?php
        });
        
        // Banner inferior
        add_action('wp_footer', function() use ($security_settings) {
            if (is_admin()) { 
                return; 
            }
            
            if (!is_user_logged_in() || !current_user_can('manage_options')) { 
                return; 
            }
            
            $mode = !empty($security_settings['migration_mode']) ? 'migration' : 'development';
            $color = $mode === 'migration' ? '#ff6b6b' : '#4ecdc4';
            $label = $mode === 'migration' ? 'MIGRATION MODE' : 'DEVELOPMENT MODE';
            
            ?>
            <div style="position: fixed; bottom: 0; left: 0; right: 0; background-color: <?php echo $color; ?>; color: white; text-align: center; padding: 3px; font-size: 10px; z-index: 999999;">
                <?php echo $label; ?>
            </div>
            <?php
        });
    }
}

add_action('init', 'flowtitude_apply_development_badges', 3);

// ===== LOGGING DE HOOKS (OPCIONAL) =====

/**
 * Registrar hooks y acciones si está activado
 */
function flowtitude_log_hooks() {
    $security_settings = get_option('flowtitude_security_settings', []);
    
    if (!empty($security_settings['log_hooks'])) {
        add_action('all', function($tag, $args) {
            flowtitude_debug_log("Hook ejecutado: $tag", 'debug', 'hooks');
        }, 10, 2);
        
        flowtitude_debug_log('Logging de hooks activado', 'info', 'hooks');
    }
}

add_action('init', 'flowtitude_log_hooks', 4);

// ===== INICIALIZACIÓN =====

// Asegurar que la función de logging esté disponible globalmente
if (!function_exists('flowtitude_debug_log')) {
    function flowtitude_debug_log($message, $type = 'info', $context = 'flowtitude') {
        // Fallback simple - solo si el logging está explícitamente activado
        $security_settings = get_option('flowtitude_security_settings', []);
        $logging_enabled = !empty($security_settings['wp_debug']) || 
                          !empty($security_settings['wp_debug_log']) || 
                          !empty($security_settings['log_hooks']);
        
        if ($logging_enabled && defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Flowtitude ' . strtoupper($type) . '] [' . $context . '] ' . $message);
        }
    }
}
