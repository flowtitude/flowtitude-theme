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

// Ruta personalizada para el log
if (!empty($opts['wp_debug_log']) && !empty($opts['wp_debug_log_path'])) {
    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', $opts['wp_debug_log_path']);
    }
}

function define_if_not_set($const, $value) {
    if (!defined($const)) {
        define($const, $value);
    }
}
