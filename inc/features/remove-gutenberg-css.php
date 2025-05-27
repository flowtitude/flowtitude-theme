<?php
if (!defined('ABSPATH')) exit;

/**
 * Desencola los estilos de Gutenberg si está activado desde los ajustes.
 */
function flowtitude_remove_gutenberg_css() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['remove_gutenberg_css'])) return;

	add_action('wp_enqueue_scripts', function () {
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
	}, 20);
}

add_action('init', 'flowtitude_remove_gutenberg_css');
