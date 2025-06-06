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

function define_if_not_set($const, $value) {
    if (!defined($const)) {
        define($const, $value);
    }
}
