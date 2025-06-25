<?php
if (!defined('ABSPATH')) exit;

/**
 * Agrega una capa CSS personalizada para estilos de Bricks Builder.
 *
 * Añade logs de depuración si no se encuentran estilos Bricks o si ocurre un error.
 *
 * @return void
 */
function flowtitude_enqueue_bricks_layer_css() {
	global $wp_styles;
	if (!isset($wp_styles) || !is_object($wp_styles)) {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('No se pudo acceder a $wp_styles. No se aplicó la capa Bricks.', 'error');
		}
		return;
	}

	$layers_order = '@layer reset, wordpres, bricks, theme, base, layouts, components, utilities, custom;';

	$styles = $wp_styles->registered;
	$first = true;
	$found = false;

	foreach ($styles as $key => $style) {
		if (
			!is_string($style->src) || empty($style->src) ||
			(strpos($style->handle, 'bricks') === false && strpos($style->src, 'bricks') === false)
		) {
			continue;
		}

		$found = true;
		$code = $first ? $layers_order . "\n" : '';
		$first = false;

		$code .= '@import url("' . esc_url($style->src) . '") layer(bricks);';

		$wp_styles->add_data($style->handle, 'after', [$code]);
		$wp_styles->registered[$key]->src = '';
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('Capa Bricks añadida al estilo: ' . $style->handle, 'success');
		}
	}
	if (!$found && function_exists('flowtitude_debug_log')) {
		flowtitude_debug_log('No se encontraron estilos Bricks para añadir la capa.', 'warning');
	}
}


function flowtitude_enable_bricks_layer_css() {
	$options = get_option('flowtitude_settings', []);
	if (!empty($options['bricks_layer'])) {
		add_action('wp_enqueue_scripts', 'flowtitude_enqueue_bricks_layer_css', 999);
	}
}
add_action('init', 'flowtitude_enable_bricks_layer_css');
