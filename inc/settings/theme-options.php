<?php
/**
 * Configuración del panel de administración de Flowtitude v2
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Agrega la página del panel de administración de Flowtitude al menú de WordPress.
 *
 * @return void
 */
function flowtitude_add_admin_page() {
	$hook = add_menu_page(
		'Flowtitude v2',
		'Flowtitude',
		'manage_options',
		'flowtitude-settings',
		'flowtitude_admin_page',
		'dashicons-admin-generic',
		30
	);
	if (function_exists('flowtitude_debug_log')) {
		if ($hook) {
			flowtitude_debug_log('Página de administración de Flowtitude añadida correctamente.', 'success');
		} else {
			flowtitude_debug_log('No se pudo añadir la página de administración de Flowtitude.', 'warning');
		}
	}
}
add_action('admin_menu', 'flowtitude_add_admin_page');

/**
 * Callback para mostrar el contenedor de la app Vue.js en la página de administración.
 * Valida permisos y añade logs de depuración.
 *
 * @return void
 */
function flowtitude_admin_page() {
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
	?>
	<div id="flowtitude-admin-app">
		<p>Cargando...</p>
	</div>
	<?php
}
