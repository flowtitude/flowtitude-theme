<?php
if (!defined('ABSPATH')) exit;

// Seguridad: evita edición de archivos desde el admin
if (!defined('DISALLOW_FILE_EDIT')) {
	define('DISALLOW_FILE_EDIT', true);
}

// === CARGA DE AJUSTES Y ENDPOINTS ===
if (!defined('FLOWTITUDE_DIR')) {
	if (function_exists('flowtitude_debug_log')) {
		flowtitude_debug_log('La constante FLOWTITUDE_DIR no está definida. No se pueden cargar los módulos.', 'error');
	}
	exit;
}

require_once FLOWTITUDE_DIR . '/inc/settings/defaults.php';
require_once FLOWTITUDE_DIR . '/inc/settings/api-endpoints.php';
require_once FLOWTITUDE_DIR . '/inc/settings/snippet-folders-endpoint.php';
require_once FLOWTITUDE_DIR . '/inc/core/layered-css.php';

// === CARGA CONDICIONAL DE FUNCIONALIDADES ===
$settings = get_option('flowtitude_settings', []);
if (function_exists('flowtitude_debug_log')) {
	flowtitude_debug_log('Ajustes de Flowtitude cargados: ' . json_encode($settings), 'info');
}

// === CARGA DE CARACTERÍSTICAS ===
// Se comprueba si el archivo existe antes de intentar cargarlo para evitar errores fatales.
$custom_dashboard_file = FLOWTITUDE_DIR . '/inc/features/custom-dashboard.php';
if (file_exists($custom_dashboard_file)) {
	flowtitude_debug_log('Cargando custom-dashboard.php', 'info', 'init');
	require_once $custom_dashboard_file;
	flowtitude_debug_log('custom-dashboard.php cargado', 'info', 'init');
}

// Cargar el proveedor de datos dinámicos personalizado para Bricks
$custom_provider_file = FLOWTITUDE_DIR . '/inc/features/custom-dynamic-provider.php';
if (file_exists($custom_provider_file)) {
	// Solo cargar si Bricks está disponible o si estamos en el admin
	if (class_exists('Bricks\Integrations\Dynamic_Data\Providers\Base') || is_admin()) {
		require_once $custom_provider_file;
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('Proveedor de datos dinámicos personalizado cargado: ' . $custom_provider_file, 'success');
		}
	} else {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('Bricks no está disponible, omitiendo carga del proveedor de datos dinámicos', 'info');
		}
	}
} else {
	if (function_exists('flowtitude_debug_log')) {
		flowtitude_debug_log('No se encontró el proveedor de datos dinámicos: ' . $custom_provider_file, 'warning');
	}
}

/**
 * Carga condicional de módulos según los ajustes del usuario.
 * Se añade logging para trazabilidad y robustez.
 */
$feature_map = [
	'revision_limit' => ['inc/features/revisions.php', 'Revisiones'],
	'move_bricks_menu' => ['inc/features/move-bricks-menu.php', 'Menú de Bricks'],
	'remove_gutenberg_css' => ['inc/features/remove-gutenberg-css.php', 'CSS de Gutenberg'],
	'remove_bricks_css' => ['inc/features/remove-bricks-css.php', 'CSS de Bricks'],
	'remove_bricks_js' => ['inc/features/remove-bricks-js.php', 'JS de Bricks'],
	'bricks_layer' => ['inc/features/bricks-layer-css.php', 'Capa CSS para Bricks'],
	'wp_layer' => ['inc/features/wp-plugins-layer-css.php', 'Capa CSS para WordPress y plugins'],
	'enable_dark_mode' => ['inc/features/frontend-darkmode.php', 'Modo oscuro'],
	'intersection_observer' => ['inc/features/tailwind-intersect.php', 'Intersection Observer'],
];
foreach ($feature_map as $setting_key => [$relative_path, $desc]) {
	if (!empty($settings[$setting_key])) {
		$feature_file = FLOWTITUDE_DIR . '/' . $relative_path;
		if (file_exists($feature_file)) {
			require_once $feature_file;
			if (function_exists('flowtitude_debug_log')) {
				flowtitude_debug_log("Funcionalidad cargada: $desc [$feature_file]", 'success');
			}
		} else {
			if (function_exists('flowtitude_debug_log')) {
				flowtitude_debug_log("No se encontró el archivo de la funcionalidad $desc: $feature_file", 'warning');
			}
		}
	}
}


// === CARGA DE SEGURIDAD ===
$security_settings = get_option('flowtitude_security_settings', []);

// Ocultar versión de WordPress
if (!empty($security_settings['hide_wp_version'])) {
	require_once FLOWTITUDE_DIR . '/inc/security/hide-wp-version.php';
}

// Desactivar XML-RPC
if (!empty($security_settings['disable_xmlrpc'])) {
	require_once FLOWTITUDE_DIR . '/inc/security/disable-xmlrpc.php';
}

// Seguridad en el login
if (!empty($security_settings['secure_login'])) {
	require_once FLOWTITUDE_DIR . '/inc/security/secure-login.php';
}