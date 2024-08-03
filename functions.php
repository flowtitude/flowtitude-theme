<?php 
require_once get_stylesheet_directory() . '/inc/settings.php';
require_once get_stylesheet_directory() . '/inc/helper.php';
ft_load_resources(__DIR__ . '/inc/settings');
ft_load_resources(__DIR__ . '/inc/custom');

function ft_enqueue() {
    wp_enqueue_style( 'main', get_stylesheet_directory_uri() . '/assets/src/scss/main.css', array(), null );
    if ( ! bricks_is_builder_main() ) {
        wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
    }
}
add_action( 'wp_enqueue_scripts', 'ft_enqueue', 100 );

ft_enqueue_recursive_assets('assets/');

ft_load_resources(__DIR__ . '/dynamic_data_tags');
//require_once get_stylesheet_directory() . '/custom_dynamic_data_tags.php';

add_action('init', function() {
    $directory = __DIR__ . '/custom_elements';
    $element_files = get_php_files($directory);

    foreach ($element_files as $file) {
        if (file_exists($file)) {
            require_once $file;
            $element_class = 'Custom_' . str_replace('-', '_', basename($file, '.php'));
            \Bricks\Elements::register_element($file, strtolower(basename($file, '.php')), $element_class);
        }
    }
}, 11);