<?php
if (!defined('ABSPATH')) exit;

/**
 * Mueve el menú de Bricks al final del menú de administración,
 * si la opción está habilitada en los ajustes del tema.
 */
function flowtitude_move_bricks_menu($menu_order) {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['move_bricks_menu'])) return $menu_order;

	if (!$menu_order) return true;

	$bricks_index = array_search('bricks', $menu_order);
	$flowtitude_index = array_search('flowtitude-settings', $menu_order);

	if ($bricks_index === false || $flowtitude_index === false) return $menu_order;

	// Quitar Bricks de su posición actual
	unset($menu_order[$bricks_index]);
	$menu_order = array_values($menu_order);

	// Insertar Bricks justo después de Flowtitude
	array_splice($menu_order, $flowtitude_index + 1, 0, ['bricks']);

	return $menu_order;
}

add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'flowtitude_move_bricks_menu');
