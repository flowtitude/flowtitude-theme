<?php
/**
 * Ocultar versión de WordPress
 * 
 * @package Flowtitude
 * @subpackage Security
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Elimina la versión de WordPress del HTML generado.
 *
 * @return string
 */
function flowtitude_remove_wp_version() {
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('Versión de WordPress ocultada por Flowtitude.', 'info');
    }
    return '';
}
add_filter('the_generator', 'flowtitude_remove_wp_version');