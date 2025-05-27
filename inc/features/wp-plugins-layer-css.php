<?php
if (!defined('ABSPATH')) exit;

/**
 * Agrega capas CSS para estilos de WordPress Core y plugins.
 */
function flowtitude_enqueue_wp_plugins_layer_css() {
	 global $wp_styles;
 
	 $layers_order = '@layer wordpress-layer, plugins-layer, bricks-layer, theme, base, components, utilities, custom;';
	 $styles = $wp_styles->registered;
	 $first = true;
 
	 $layer_identifiers = [
		 'wordpress-layer' => ['wp-', 'admin-bar', 'dashicons', 'editor', 'common', 'forms'],
	 ];
 
	 foreach ($styles as $key => $style) {
		 if (!is_string($style->src) || empty($style->src)) continue;
 
		 $matched_layer = 'plugins-layer';
		 foreach ($layer_identifiers as $layer => $identifiers) {
			 foreach ($identifiers as $identifier) {
				 if (strpos($style->handle, $identifier) !== false || strpos($style->src, $identifier) !== false) {
					 $matched_layer = $layer;
					 break 2;
				 }
			 }
		 }
 
		 $code = $first ? $layers_order . "\n" : '';
		 $first = false;
 
		 $code .= '@import url("' . esc_url($style->src) . '") layer(' . $matched_layer . ');';
 
		 $wp_styles->add_data($style->handle, 'after', [$code]);
		 $wp_styles->registered[$key]->src = '';
	 }
 }

function flowtitude_enable_wp_plugins_layer_css() {
	$options = get_option('flowtitude_settings', []);
	if (!empty($options['wp_layer'])) {
		add_action('wp_enqueue_scripts', 'flowtitude_enqueue_wp_plugins_layer_css', 999);
	}
}
add_action('init', 'flowtitude_enable_wp_plugins_layer_css');
