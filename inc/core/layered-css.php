<?php
if (!defined('ABSPATH')) exit;

/**
 * Encola todas las capas CSS en el orden correcto, independientemente del contexto.
 * @param string $context Sufijo para distinguir el contexto (ej: 'dashboard', 'frontend')
 */
function flowtitude_enqueue_layered_css($context = 'frontend') {
    // >> Condición de seguridad: No ejecutar nunca en el panel de administración.
    if (is_admin()) {
        return;
    }

    global $wp_styles;
    if (!isset($wp_styles) || !is_object($wp_styles)) return;
    $layers_order = '@layer wordpress-layer, plugins-layer, bricks, theme, base, layouts, components, utilities, custom;';
    $layer_files = [
        'wordpress-layer' => get_template_directory_uri() . '/assets/css/wordpress.css',
        'plugins-layer' => get_template_directory_uri() . '/assets/css/plugins.css',
        'bricks' => get_template_directory_uri() . '/assets/css/bricks.css',
        'theme' => get_template_directory_uri() . '/assets/css/theme.css',
        'base' => get_template_directory_uri() . '/assets/css/base.css',
        'layouts' => get_template_directory_uri() . '/assets/css/layouts.css',
        'components' => get_template_directory_uri() . '/assets/css/components.css',
        'utilities' => get_template_directory_uri() . '/assets/css/utilities.css',
        'custom' => get_template_directory_uri() . '/assets/css/custom.css',
    ];
    $first = true;
    foreach ($layer_files as $layer => $file_url) {
        $code = $first ? $layers_order . "\n" : '';
        $first = false;
        $code .= '@import url("' . esc_url($file_url) . '") layer(' . $layer . ');';
        $handle = 'flowtitude-' . $context . '-' . $layer;
        wp_register_style($handle, $file_url, [], null);
        wp_enqueue_style($handle);
        $wp_styles->add_data($handle, 'after', [$code]);
    }
} 