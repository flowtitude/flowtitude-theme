<?php
if (!defined('ABSPATH')) exit;

/**
 * Desencola los scripts de Bricks Builder si está activado desde los ajustes.
 */
function flowtitude_remove_bricks_js() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['remove_bricks_js'])) return;

	add_action('wp_enqueue_scripts', function () {
		wp_dequeue_script('bricks-frontend');
		wp_dequeue_script('bricks-scripts'); // Por si acaso
	}, 20);
}

add_action('init', 'flowtitude_remove_bricks_js');
