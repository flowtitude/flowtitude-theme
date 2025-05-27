<?php
if (!defined('ABSPATH')) exit;

/**
 * Activa automáticamente los componentes de Bricks añadiendo la categoría Flowtitude.
 *
 * @return void
 */
function flowtitude_activate_bricks_components() {
    if (!class_exists('Bricks\Elements')) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Bricks\Elements no está disponible. No se activan componentes Flowtitude.', 'warning');
        }
        return;
    }
    add_filter('bricks/builder/categories', function($categories) {
        $categories['flowtitude'] = 'Flowtitude';
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Categoría Flowtitude añadida a Bricks.', 'info');
        }
        return $categories;
    });
}

add_action('init', 'flowtitude_activate_bricks_components');

/**
 * Registra los componentes de Bricks creados por Flowtitude.
 *
 * @return void
 */
function flowtitude_register_bricks_components() {
    if (!class_exists('Bricks\Elements')) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('Bricks\Elements no está disponible. No se registran componentes Flowtitude.', 'warning');
        }
        return;
    }
    if (!defined('FLOWTITUDE_DIR')) {
        if (function_exists('flowtitude_debug_log')) {
            flowtitude_debug_log('La constante FLOWTITUDE_DIR no está definida. No se pueden registrar componentes.', 'error');
        }
        return;
    }
    $components_dir = FLOWTITUDE_DIR . '/components';
    // Permitir modificar el array de componentes desde filtros
    $test_components = apply_filters('flowtitude_bricks_components', [
        'test-alert' => 'Flowtitude_Test_Alert',
        'test-toggle' => 'Flowtitude_Test_Toggle'
    ]);
    foreach ($test_components as $dir => $class) {
        $component_file = $components_dir . "/{$dir}/component.php";
        if (!file_exists($component_file)) {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('No se encontró el archivo del componente: ' . $component_file, 'warning');
            }
            continue;
        }
        require_once $component_file;
        if (class_exists($class)) {
            \Bricks\Elements::register_element($class);
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('Componente Bricks registrado: ' . $class, 'success');
            }
        } else {
            if (function_exists('flowtitude_debug_log')) {
                flowtitude_debug_log('No se encontró la clase del componente tras incluir el archivo: ' . $class, 'warning');
            }
        }
    }
}
add_action('init', 'flowtitude_register_bricks_components');
