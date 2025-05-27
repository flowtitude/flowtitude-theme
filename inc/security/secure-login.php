<?php
/**
 * Mejorar seguridad del login
 * 
 * @package Flowtitude
 * @subpackage Security
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Oculta los mensajes de error de login para no revelar información sobre usuarios.
 *
 * @return string Mensaje genérico de error
 */
function flowtitude_custom_login_errors() {
    return __('Acceso denegado.', 'flowtitude');
}
add_filter('login_errors', 'flowtitude_custom_login_errors');

/**
 * Limita los intentos de login por IP y registra logs de intentos fallidos/bloqueos.
 * Nota: Este filtro puede interferir con otros plugins de autenticación o seguridad.
 *
 * @param null|WP_User|WP_Error $user
 * @return WP_User|WP_Error
 */
function flowtitude_limit_login_attempts($user) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if ($ip_address === 'unknown' && function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Intento de login con IP desconocida.', 'warning');
    }
    $attempts = get_transient('login_attempts_' . $ip_address) ?: 0;

    if ($attempts >= 5) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("IP bloqueada por demasiados intentos: $ip_address", 'warning');
        }
        return new WP_Error('too_many_attempts', __('Demasiados intentos fallidos. Inténtalo de nuevo más tarde.', 'flowtitude'));
    }

    // Validar que existen los datos POST antes de usarlos
    if (!isset($_POST['log']) || !isset($_POST['pwd'])) {
        return $user;
    }

    $user_attempt = wp_authenticate_username_password(null, $_POST['log'], $_POST['pwd']);

    if (is_wp_error($user_attempt)) {
        $attempts++;
        set_transient('login_attempts_' . $ip_address, $attempts, 30 * MINUTE_IN_SECONDS);
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("Intento de login fallido desde $ip_address. Intentos: $attempts", 'warning');
        }
    } else {
        delete_transient('login_attempts_' . $ip_address);
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log("Login exitoso desde $ip_address para usuario: " . $_POST['log'], 'info');
            flowtitude_debug_log("Contador de intentos de login reseteado para $ip_address.", 'info');
        }
    }

    return $user_attempt;
}
add_filter('authenticate', 'flowtitude_limit_login_attempts', 30, 1);