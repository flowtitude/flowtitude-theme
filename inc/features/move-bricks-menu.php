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
	// Buscar el primer menú cuyo slug empiece por 'bricks'
	$bricks_index = false;
	foreach ($menu_order as $i => $slug) {
		if (strpos($slug, 'bricks') === 0) {
			$bricks_index = $i;
			break;
		}
	}
	if ($bricks_index === false || $flowtitude_index === false) return $menu_order;

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

add_action('admin_menu', function() {
	$options = get_option('flowtitude_settings', []);
	if (empty($options['move_bricks_menu'])) return;

	// Eliminar el menú de Bricks si existe
	remove_menu_page('bricks');

	// Icono SVG base64 original de Bricks
	$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMzVweCIgaGVpZ2h0PSI0NXB4IiB2aWV3Qm94PSIwIDAgMzUgNDUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDU5LjEgKDg2MTQ0KSAtIGh0dHBzOi8vc2tldGNoLmNvbSAtLT4KICAgIDx0aXRsZT5iPC90aXRsZT4KICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPgogICAgPGcgaWQ9IkxvZ29zLC1GYXZpY29uIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0iI2E3YWFhZCIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0iRmF2aWNvbi0oNjR4NjQpIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMTYuMDAwMDAwLCAtMTEuMDAwMDAwKSIgZmlsbD0iI2E3YWFhZCIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICAgICAgPHBhdGggZD0iTTI1LjE4NzUsMTEuMzQzNzUgTDI1LjkzNzUsMTEuODEyNSBMMjUuOTM3NSwyNC44NDM3NSBDMjguNTgzMzQ2NiwyMy4wOTM3NDEzIDMxLjUxMDQwMDYsMjIuMjE4NzUgMzQuNzE4NzUsMjIuMjE4NzUgQzM5LjM0Mzc3MzEsMjIuMjE4NzUgNDMuMTc3MDY4MSwyMy44MzMzMTcyIDQ2LjIxODc1LDI3LjA2MjUgQzQ5LjIxODc2NSwzMC4yOTE2ODI4IDUwLjcxODc1LDM0LjI3MDgwOTcgNTAuNzE4NzUsMzkgQzUwLjcxODc1LDQzLjc1MDAyMzcgNDkuMjA4MzQ4NCw0Ny43MjkxNTA2IDQ2LjE4NzUsNTAuOTM3NSBDNDMuMTQ1ODE4MSw1NC4xNjY2ODI4IDM5LjMyMjkzOTcsNTUuNzgxMjUgMzQuNzE4NzUsNTUuNzgxMjUgQzMwLjY5Nzg5NjYsNTUuNzgxMjUgMjcuMjYwNDMwOSw1NC4zNDM3NjQ0IDI0LjQwNjI1LDUxLjQ2ODc1IEwyNC40MDYyNSw1NSBMMTYuMDMxMjUsNTUgTDE2LjAzMTI1LDEyLjM3NSBMMjUuMTg3NSwxMS4zNDM3NSBaIE0zMy4xMjUsMzAuNjg3NSBDMzAuOTE2NjU1NiwzMC42ODc1IDI5LjA3MjkyNDEsMzEuNDM3NDkyNSAyNy41OTM3NSwzMi45Mzc1IEMyNi4xMTQ1NzU5LDM0LjQ3OTE3NDQgMjUuMzc1LDM2LjQ5OTk4NzUgMjUuMzc1LDM5IEMyNS4zNzUsNDEuNTAwMDEyNSAyNi4xMTQ1NzU5LDQzLjUxMDQwOTEgMjcuNTkzNzUsNDUuMDMxMjUgQzI5LjA1MjA5MDYsNDYuNTUyMDkwOSAzMC44OTU4MjIyLDQ3LjMxMjUgMzMuMTI1LDQ3LjMxMjUgQzM1LjQ3OTE3ODQsNDcuMzEyNSAzNy4zODU0MDk0LDQ2LjUyMDg0MTMgMzguODQzNzUsNDQuOTM3NSBDNDAuMjgxMjU3Miw0My4zNzQ5OTIyIDQxLDQxLjM5NTg0NTMgNDEsMzkgQzQxLDM2LjYwNDE1NDcgNDAuMjcwODQwNiwzNC42MTQ1OTEzIDM4LjgxMjUsMzMuMDMxMjUgQzM3LjM1NDE1OTQsMzEuNDY4NzQyMiAzNS40NTgzNDUsMzAuNjg3NSAzMy4xMjUsMzAuNjg3NSBaIiBpZD0iYiI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+';

	if (function_exists('bricks_admin_menu')) {
		bricks_admin_menu(62);
	} else {
		add_menu_page(
			'Bricks',
			'Bricks',
			'manage_options',
			'bricks',
			'',
			$icon,
			62
		);
	}
}, 100);
