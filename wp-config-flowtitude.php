<?php
/**
 * Configuración de logging para Flowtitude
 * 
 * Copia estas líneas a tu wp-config.php para controlar el logging del tema:
 * 
 * // ===== CONFIGURACIÓN DE FLOWTITUDE =====
 * // Desactivar todos los logs (por defecto)
 * define('FLOWTITUDE_LOG', false);
 * 
 * // Para activar solo errores críticos
 * define('FLOWTITUDE_LOG', true);
 * define('FLOWTITUDE_LOG_LEVEL', 'error'); // error, warning, info, debug
 * 
 * // Para activar logs específicos por módulos
 * define('FLOWTITUDE_LOG', true);
 * define('FLOWTITUDE_LOG_LEVEL', 'info');
 * define('FLOWTITUDE_LOG_DASHBOARD', true);   // Logs del dashboard personalizado
 * define('FLOWTITUDE_LOG_ENDPOINTS', true);   // Logs de endpoints REST API
 * define('FLOWTITUDE_LOG_SNIPPETS', true);    // Logs de gestión de snippets
 * define('FLOWTITUDE_LOG_SECURITY', true);    // Logs de configuraciones de seguridad
 * define('FLOWTITUDE_LOG_BRICKS', true);      // Logs de integración con Bricks
 * define('FLOWTITUDE_LOG_HOOKS', true);       // Logs de hooks y acciones
 * 
 * // Para activar logs completos (solo para depuración)
 * define('FLOWTITUDE_LOG', true);
 * define('FLOWTITUDE_LOG_LEVEL', 'debug');
 * define('FLOWTITUDE_LOG_TO_FILE', true); // Archivo separado en wp-content/flowtitude-debug.log
 * 
 * // ===== CONFIGURACIÓN DE FLOWTITUDE =====
 */

// Configuración por defecto (logs desactivados)
if (!defined('FLOWTITUDE_LOG')) {
    define('FLOWTITUDE_LOG', false);
}

if (!defined('FLOWTITUDE_LOG_LEVEL')) {
    define('FLOWTITUDE_LOG_LEVEL', 'error');
}

if (!defined('FLOWTITUDE_LOG_TO_FILE')) {
    define('FLOWTITUDE_LOG_TO_FILE', false);
}

// Constantes por módulos (todas desactivadas por defecto)
if (!defined('FLOWTITUDE_LOG_DASHBOARD')) {
    define('FLOWTITUDE_LOG_DASHBOARD', false);
}

if (!defined('FLOWTITUDE_LOG_ENDPOINTS')) {
    define('FLOWTITUDE_LOG_ENDPOINTS', false);
}

if (!defined('FLOWTITUDE_LOG_SNIPPETS')) {
    define('FLOWTITUDE_LOG_SNIPPETS', false);
}

if (!defined('FLOWTITUDE_LOG_SECURITY')) {
    define('FLOWTITUDE_LOG_SECURITY', false);
}

if (!defined('FLOWTITUDE_LOG_BRICKS')) {
    define('FLOWTITUDE_LOG_BRICKS', false);
}

if (!defined('FLOWTITUDE_LOG_HOOKS')) {
    define('FLOWTITUDE_LOG_HOOKS', false);
}

// Configuración adicional del tema
if (!defined('FLOWTITUDE_VERSION')) {
    define('FLOWTITUDE_VERSION', '2.0.0');
}

if (!defined('FLOWTITUDE_MIN_WP_VERSION')) {
    define('FLOWTITUDE_MIN_WP_VERSION', '6.0');
}

if (!defined('FLOWTITUDE_MIN_PHP_VERSION')) {
    define('FLOWTITUDE_MIN_PHP_VERSION', '8.0');
} 