<?php
if (!defined('ABSPATH')) exit;

/**
 * Desencola los estilos de Bricks Builder si está activado desde los ajustes.
 *
 * Añade logs de depuración para éxito o advertencia.
 *
 * @return void
 */
function flowtitude_remove_bricks_css() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['remove_bricks_css'])) return;

	add_action('wp_enqueue_scripts', function () {
		if (wp_style_is('bricks-builder-styles', 'enqueued')) {
			wp_dequeue_style('bricks-builder-styles');
			if (function_exists('flowtitude_debug_log')) {
				flowtitude_debug_log('Estilo bricks-builder-styles desencolado correctamente.', 'success');
			}
		} else {
			if (function_exists('flowtitude_debug_log')) {
				flowtitude_debug_log('El estilo bricks-builder-styles no estaba encolado.', 'warning');
			}
		}
	}, 20);
}

add_action('init', 'flowtitude_remove_bricks_css');
