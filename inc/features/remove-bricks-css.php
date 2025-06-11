<?php
if (!defined('ABSPATH')) exit;

/**
 * Devuelve true si estamos en el editor de Bricks Builder.
 */
function flowtitude_is_bricks_editor() {
	// Bricks añade el parámetro bricks=run en el editor visual
	if (isset($_GET['bricks']) && $_GET['bricks'] === 'run') {
		return true;
	}
	// Algunos casos podrían llevar bricks=true o bricks_welcome
	if (isset($_GET['bricks']) && $_GET['bricks'] === 'true') {
		return true;
	}
	// Opcional: comprueba el endpoint /bricks/
	if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/bricks/') !== false) {
		return true;
	}
	return false;
}

/**
 * Desencola y desregistra estilos de Bricks Builder si está activado desde los ajustes,
 * solo en el frontend (nunca en el editor de Bricks).
 */
function flowtitude_remove_bricks_css() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['remove_bricks_css'])) return;

	$styles = [
		'bricks-builder-styles',
		'bricks-frontend',
		'bricks-default-content',
		'bricks-element-posts',
		'bricks-isotope',
		'bricks-element-post-author',
		'bricks-element-post-comments',
		'bricks-element-post-navigation',
		'bricks-element-post-sharing',
		'bricks-element-post-taxonomy',
		'bricks-element-related-posts',
		'bricks-404'
	];

	add_action('wp_enqueue_scripts', function () use ($styles) {
		// NO HACER NADA EN EL EDITOR DE BRICKS
		if (flowtitude_is_bricks_editor()) {
			if (function_exists('flowtitude_debug_log')) {
				flowtitude_debug_log("Desencolado de estilos Bricks saltado: estamos en el editor de Bricks.", 'info');
			}
			return;
		}

		foreach ($styles as $style) {
			if (wp_style_is($style, 'enqueued')) {
				wp_dequeue_style($style);
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("Estilo $style desencolado correctamente.", 'success');
				}
			} else {
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("El estilo $style no estaba encolado.", 'warning');
				}
			}

			if (wp_style_is($style, 'registered')) {
				wp_deregister_style($style);
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("Estilo $style desregistrado correctamente.", 'success');
				}
			} else {
				if (function_exists('flowtitude_debug_log')) {
					flowtitude_debug_log("El estilo $style no estaba registrado.", 'warning');
				}
			}
		}
	}, 20);
}

add_action('init', 'flowtitude_remove_bricks_css');
