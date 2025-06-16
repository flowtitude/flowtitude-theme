<?php
if (!defined('ABSPATH')) exit;

/**
 * Obtiene la configuración por defecto del tema
 * @return array Configuración por defecto
 */
function flowtitude_get_settings_defaults() {
	return [
		// Configuración general
		'site_title' => get_bloginfo('name'),
		'custom_message' => 'Bienvenido a Flowtitude',
		'custom_dashboard_template' => '',
		
		// Colores
		'primary_color' => '#1E40AF',
		'secondary_color' => '#9333EA',
		
		// Características
		'enable_feature' => false,
		
		// Capas
		'bricks_layer' => false,
		'wp_layer' => false,
		
		// Directorios
		'snippets_dir' => 'snippets',
		'bricks_dir' => 'bricks',
		
		// Versión de la configuración
		'version' => FLOWTITUDE_VERSION
	];
}

/**
 * Actualiza la configuración del tema si la versión ha cambiado o no existe.
 * Añade logs de depuración y validación defensiva para robustez.
 *
 * @return void
 */
function flowtitude_maybe_update_settings() {
	if (!defined('FLOWTITUDE_VERSION')) {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('La constante FLOWTITUDE_VERSION no está definida. No se puede actualizar la configuración.', 'error');
		}
		return;
	}
	$current_settings = get_option('flowtitude_settings', []);
	$default_settings = flowtitude_get_settings_defaults();

	// Si no hay versión o es diferente, actualizar
	if (empty($current_settings['version']) || $current_settings['version'] !== FLOWTITUDE_VERSION) {
		$updated_settings = array_merge($default_settings, $current_settings);
		$updated_settings['version'] = FLOWTITUDE_VERSION;
		$success = update_option('flowtitude_settings', $updated_settings);
		if (function_exists('flowtitude_debug_log')) {
			if ($success) {
				flowtitude_debug_log('Configuración actualizada a la versión ' . FLOWTITUDE_VERSION, 'success');
			} else {
				flowtitude_debug_log('Fallo al actualizar la configuración a la versión ' . FLOWTITUDE_VERSION, 'warning');
			}
		}
	} else {
		if (function_exists('flowtitude_debug_log')) {
			flowtitude_debug_log('La configuración ya está actualizada a la versión ' . FLOWTITUDE_VERSION, 'info');
		}
	}
}
add_action('after_setup_theme', 'flowtitude_maybe_update_settings');