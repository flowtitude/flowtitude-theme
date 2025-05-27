<?php
if (!defined('ABSPATH')) exit;

// Agrega el ítem de menú principal de Flowtitude
add_action('admin_menu', function () {
	$icon_path = get_stylesheet_directory() . '/admin-panel/assets/icon.png';
	$icon_url = get_stylesheet_directory_uri() . '/admin-panel/assets/icon.png';
	if (!file_exists($icon_path)) {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('El icono personalizado de Flowtitude no se encontró en: ' . $icon_path, 'warning');
		}
		$icon_url = 'dashicons-admin-generic';
	}
	$hook = add_menu_page(
		'Flowtitude', // Título de la página
		'Flowtitude', // Texto del menú
		'manage_options', // Capacidad requerida
		'flowtitude-settings', // Slug
		'flowtitude_render_admin_page', // Callback
		$icon_url,
		61 // Posición
	);
	if (function_exists('flowtitude_debug_log')) {
		if ($hook) {
			flowtitude_debug_log('Menú de administración de Flowtitude añadido correctamente.', 'success');
		} else {
			flowtitude_debug_log('No se pudo añadir el menú de administración de Flowtitude.', 'warning');
		}
	}
});

/**
 * Callback que renderiza la SPA de Vue para el panel de Flowtitude.
 * Valida permisos y añade logs de depuración.
 *
 * @return void
 */
function flowtitude_render_admin_page() {
	if (!current_user_can('manage_options')) {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('Intento de acceso no autorizado al panel de administración de Flowtitude.', 'warning');
		}
		echo '<div class="notice notice-error"><p>No tienes permisos para acceder a esta página.</p></div>';
		return;
	}
	if (function_exists('flowtitude_debug_log')) {
		flowtitude_debug_log('Panel de administración de Flowtitude cargado para el usuario: ' . wp_get_current_user()->user_login, 'info');
	}
	echo '<div id="flowtitude-admin-app">Cargando panel de Flowtitude...</div>';
}
