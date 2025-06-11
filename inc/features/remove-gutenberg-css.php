<?php
if (!defined('ABSPATH')) exit;

/**
 * Desencola y desregistra los estilos de Gutenberg si está activado desde los ajustes.
 */
function flowtitude_remove_gutenberg_css() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['remove_gutenberg_css'])) return;

	// Listado de estilos de Gutenberg a gestionar
	$styles = [
		'wp-block-library',
		'wp-block-library-theme',
		'classic-theme-styles',
		'global-styles'
	];

	add_action('wp_enqueue_scripts', function () use ($styles) {
		foreach ($styles as $style) {
			// Desencolar si está encolado
			if (wp_style_is($style, 'enqueued')) {
				wp_dequeue_style($style);
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("Estilo $style de Gutenberg desencolado correctamente.", 'success');
				}
			} else {
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("El estilo $style de Gutenberg no estaba encolado.", 'warning');
				}
			}

			// Desregistrar si está registrado
			if (wp_style_is($style, 'registered')) {
				wp_deregister_style($style);
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("Estilo $style de Gutenberg desregistrado correctamente.", 'success');
				}
			} else {
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("El estilo $style de Gutenberg no estaba registrado.", 'warning');
				}
			}
		}
	}, 20);
}

add_action('init', 'flowtitude_remove_gutenberg_css');