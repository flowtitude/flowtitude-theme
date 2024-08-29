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

function ft_enqueue_admin() {
    wp_enqueue_style('ft-admin-style', get_stylesheet_directory_uri() . '/assets/backend/css/admin.css');
}
add_action('admin_enqueue_scripts', 'ft_enqueue_admin');

ft_enqueue_recursive_assets('assets/css/');
ft_enqueue_recursive_assets('assets/js/');

ft_load_resources(__DIR__ . '/dynamic_data_tags');
ft_load_resources(__DIR__ . '/conditionals');

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

function ft_remove_bricks_frontend_css_manually() {
    echo "
    <script>
    document.querySelector('link[href*=\"frontend.min.css\"]').remove();
    </script>
    ";
}
add_action('admin_footer', 'ft_remove_bricks_frontend_css_manually', 100);
