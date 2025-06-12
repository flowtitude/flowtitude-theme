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

	// Buscar el slug de Flowtitude
	$flowtitude_index = array_search('flowtitude-settings', $menu_order);
	if ($flowtitude_index === false) return $menu_order;

	// Buscar el primer menú cuyo slug empiece por 'bricks'
	$bricks_index = false;
	foreach ($menu_order as $i => $slug) {
		if (strpos($slug, 'bricks') === 0) {
			$bricks_index = $i;
			break;
		}
	}
	if ($bricks_index === false) return $menu_order;

	// Quitar Bricks de su posición actual
	$bricks_slug = $menu_order[$bricks_index];
	unset($menu_order[$bricks_index]);
	$menu_order = array_values($menu_order);

	// Insertar Bricks justo después de Flowtitude
	array_splice($menu_order, $flowtitude_index + 1, 0, [$bricks_slug]);

	return $menu_order;
}

add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', 'flowtitude_move_bricks_menu');
