<?php
function custom_revisions_limit($num, $post) {
    $options = get_option('ft_settings');
    $limit = isset($options['revisions_limit']) ? intval($options['revisions_limit']) : 5;

    // Obtiene el tipo de post
    $post_type = get_post_type($post);

    // Verifica si el post es una página o plantilla de Bricks Builder
    if ( 'post' === $post_type || 'page' === $post_type || 'bricks_template' === $post_type ) {
        return $limit;
    }
    
    // Devuelve el valor original si no se trata de un post, página o plantilla de Bricks Builder
    return $num;
}
add_filter('wp_revisions_to_keep', 'custom_revisions_limit', 10, 2);

function ft_revisions_limit_setting_field() {
    add_settings_field(
        'ft_revisions_limit',
        'Limit Revisions',
        'ft_revisions_limit_callback',
        'ft-settings',
        'ft_general_section'
    );
}
add_action('admin_init', 'ft_revisions_limit_setting_field');

function ft_revisions_limit_callback() {
    $options = get_option('ft_settings');
    ?>
    <input type="number" name="ft_settings[revisions_limit]" value="<?php echo isset($options['revisions_limit']) ? esc_attr($options['revisions_limit']) : 5; ?>" min="0">
    <p class="label">Set the maximum number of revisions to keep for posts, pages, and Bricks Builder content. Default is 5.</p>
    <?php
}

function ft_save_revisions_limit($num, $post) {
    $options = get_option('ft_settings');
    $limit = isset($options['revisions_limit']) ? intval($options['revisions_limit']) : $num;
    
    return $limit;
}
add_filter('wp_revisions_to_keep', 'ft_save_revisions_limit', 10, 2);

  