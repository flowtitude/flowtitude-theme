<?php
if (!defined('ABSPATH')) exit;

/**
 * Mueve el menú de Bricks al final del menú de administración,
 * si la opción está habilitada en los ajustes del tema.
 */
function flowtitude_move_bricks_menu($menu_order) {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['move_bricks_menu'])) return $menu_order;

	// Aseguramos que la orden del menú existe
	if (!$menu_order) return true;

	foreach ($menu_order as $index => $item) {
		if ($item === 'bricks') {
			unset($menu_order[$index]);
			$bricks_menu = $item;
			break;
		}
	}

	// Añadir al final si se ha encontrado
	if (isset($bricks_menu)) {
		$menu_order[] = $bricks_menu;
	}

	return $menu_order;
}

add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'flowtitude_move_bricks_menu');
