<?php
if (!defined('ABSPATH')) exit;

/**
 * Agrega capas CSS para estilos de WordPress Core y plugins.
 */
function flowtitude_enqueue_wp_plugins_layer_css() {
	flowtitude_enqueue_layered_css('frontend');
}
add_action('wp_enqueue_scripts', 'flowtitude_enqueue_wp_plugins_layer_css', 999);

function flowtitude_enable_wp_plugins_layer_css() {
	$options = get_option('flowtitude_settings', []);
	if (!empty($options['wp_layer'])) {
		add_action('wp_enqueue_scripts', 'flowtitude_enqueue_wp_plugins_layer_css', 999);
	}
}
add_action('init', 'flowtitude_enable_wp_plugins_layer_css');
