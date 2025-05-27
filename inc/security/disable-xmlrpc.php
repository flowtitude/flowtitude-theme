<?php
/**
 * Desactivar XML-RPC
 * 
 * @package Flowtitude
 * @subpackage Security
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Desactiva XML-RPC para mejorar la seguridad del sitio.
 */
add_filter('xmlrpc_enabled', function($enabled) {
    if (function_exists('flowtitude_debug_log')) {
        flowtitude_debug_log('XML-RPC desactivado por Flowtitude.', 'info');
    }
    return false;
});