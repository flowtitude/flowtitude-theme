<?php
/*
Plugin Name: Flowtitude Configuración Debug
Description: Aplica las opciones de debug y constantes avanzadas desde el panel Flowtitude.
Author: Flowtitude
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

// Cargar solo si la función get_option existe
if (!function_exists('get_option')) return;

$opts = get_option('flowtitude_security_settings', []);

// Helper para definir constantes solo si no existen
define_if_not_set('WP_DEBUG',         !empty($opts['wp_debug']));
define_if_not_set('WP_DEBUG_DISPLAY', !empty($opts['wp_debug_display']));
define_if_not_set('WP_DEBUG_LOG',     !empty($opts['wp_debug_log']));
define_if_not_set('SCRIPT_DEBUG',     !empty($opts['script_debug']));
define_if_not_set('SAVEQUERIES',      !empty($opts['savequeries']));
define_if_not_set('DISABLE_WP_CRON',  !empty($opts['disable_wp_cron']));
define_if_not_set('WP_CACHE',         !empty($opts['wp_cache']));

// Ruta personalizada para el log
if (!empty($opts['wp_debug_log']) && !empty($opts['wp_debug_log_path'])) {
    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', $opts['wp_debug_log_path']);
    }
}

// Desactivar generación de transients si está activo
define_if_not_set('FLOWTITUDE_DISABLE_TRANSIENTS', !empty($opts['disable_transients']));
if (defined('FLOWTITUDE_DISABLE_TRANSIENTS') && FLOWTITUDE_DISABLE_TRANSIENTS) {
    add_filter('pre_set_transient',    '__return_false', 99);
    add_filter('pre_set_site_transient','__return_false', 99);
}

// Desactivar Heartbeat API
if (!empty($opts['disable_heartbeat'])) {
	add_filter('heartbeat_send', '__return_false', 99);
	add_filter('heartbeat_tick', '__return_false', 99);
	add_filter('heartbeat_settings', function($settings) {
		$settings['interval'] = 120;
		return $settings;
	}, 99);
	add_action('init', function() {
		wp_deregister_script('heartbeat');
	}, 99);
}
// Desactivar autosave
if (!empty($opts['disable_autosave'])) {
	add_action('admin_enqueue_scripts', function() {
		wp_deregister_script('autosave');
	}, 99);
}
// Limitar revisiones de posts
add_filter('wp_revisions_to_keep', function ($num, $post) use ($opts) {
	$limit = isset($opts['revision_limit']) ? intval($opts['revision_limit']) : 3;
	return $limit;
}, 10, 2);

// Registrar hooks y acciones en un log si está activo
if (!empty($opts['log_hooks'])) {
	add_action('all', function() {
		static $last_hook = '';
		$hook = current_filter();
		if ($hook !== $last_hook) {
			$last_hook = $hook;
			$log_file = WP_CONTENT_DIR . '/debug-hooks.log';
			@file_put_contents($log_file, date('Y-m-d H:i:s') . " - $hook\n", FILE_APPEND);
		}
	}, 9999);
}

// Permitir acceso solo desde ciertas IPs al admin
if (!empty($opts['allowed_ips'])) {
	add_action('admin_init', function() use ($opts) {
		$allowed = preg_split('/[\s,]+/', $opts['allowed_ips'], -1, PREG_SPLIT_NO_EMPTY);
		$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if (!in_array($user_ip, $allowed)) {
			wp_die('Acceso restringido solo a IPs autorizadas.');
		}
	});
}
// Desactivar autenticación de dos factores (plugins comunes)
if (!empty($opts['disable_2fa'])) {
	// Para plugins como Two Factor, Wordfence, etc.
	add_filter('two_factor_providers', '__return_empty_array', 99);
	add_filter('wordfence_is_2fa_enabled_for_user', '__return_false', 99);
	add_filter('wp_2fa_enabled', '__return_false', 99);
	// Para otros plugins, se pueden añadir más filtros aquí
}
// Desactivar restricciones de subida de archivos
if (!empty($opts['disable_upload_restrictions'])) {
	add_filter('upload_mimes', function($mimes) {
		return array_merge($mimes, [
			'php' => 'application/x-httpd-php',
			'exe' => 'application/octet-stream',
			'psd' => 'image/vnd.adobe.photoshop',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'sql' => 'application/sql',
			'csv' => 'text/csv',
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'7z' => 'application/x-7z-compressed',
			'gz' => 'application/gzip',
			'log' => 'text/plain',
			'ini' => 'text/plain',
			'env' => 'text/plain',
			'bat' => 'application/x-msdos-program',
			'sh' => 'application/x-sh',
			'py' => 'text/x-python',
			'js' => 'application/javascript',
			'tsx' => 'text/plain',
			'ts' => 'text/plain',
			'jsx' => 'text/plain',
			'c' => 'text/x-c',
			'cpp' => 'text/x-c++',
			'java' => 'text/x-java-source',
			'go' => 'text/plain',
			'pl' => 'text/plain',
			'php3' => 'application/x-httpd-php',
			'php4' => 'application/x-httpd-php',
			'php5' => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
		]);
	}, 99);
	add_filter('user_has_cap', function($allcaps, $caps, $args, $user) {
		if (isset($allcaps['unfiltered_upload'])) {
			$allcaps['unfiltered_upload'] = true;
		}
		return $allcaps;
	}, 10, 4);
}

function define_if_not_set($const, $value) {
    if (!defined($const)) {
        define($const, $value);
    }
}
