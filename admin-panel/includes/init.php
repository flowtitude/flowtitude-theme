<?php
/**
 * Inicialización del panel de administración
 */

// Cargar clases necesarias
require_once __DIR__ . '/class-flowtitude-color.php';

// Registrar manejadores AJAX
add_action('wp_ajax_flowtitude_get_colors', 'flowtitude_get_colors');
add_action('wp_ajax_flowtitude_save_colors', 'flowtitude_save_colors');
add_action('wp_ajax_flowtitude_get_typography', 'flowtitude_get_typography');
add_action('wp_ajax_flowtitude_save_typography', 'flowtitude_save_typography');
add_action('wp_ajax_flowtitude_get_tailwind_config', 'flowtitude_get_tailwind_config');
add_action('wp_ajax_flowtitude_update_tailwind_config', 'flowtitude_update_tailwind_config');
add_action('wp_ajax_flowtitude_get_theme_colors', 'flowtitude_get_theme_colors');
add_action('wp_ajax_flowtitude_get_theme_typography', 'flowtitude_get_theme_typography');
add_action('wp_ajax_flowtitude_get_theme_spacing', 'flowtitude_get_theme_spacing');
add_action('wp_ajax_flowtitude_get_theme_layout', 'flowtitude_get_theme_layout');
add_action('wp_ajax_flowtitude_save_theme_colors', 'flowtitude_save_theme_colors');
add_action('wp_ajax_flowtitude_save_spacing', 'flowtitude_save_spacing');
add_action('wp_ajax_flowtitude_save_layout', 'flowtitude_save_layout');
add_action('wp_ajax_flowtitude_save_typography', 'flowtitude_save_typography'); 