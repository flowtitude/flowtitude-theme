<?php 
// {current_user_first_name}
// Get current user first_name or get user_login name as default
// Adds a new tag 'current_user_first_name' to the Bricks Builder dynamic tags list.
add_filter( 'bricks/dynamic_tags_list', 'add_current_user_first_name_tag_to_builder' );
function add_current_user_first_name_tag_to_builder( $tags ) {
    $tags[] = [
        'name'  => '{current_user_first_name}',
        'label' => 'Current User First Name',
        'group' => 'Flowtitude',
    ];

    return $tags;
}

// Retrieves the first name of the current user or falls back to the username.
function get_current_user_first_name() {
    $current_user = wp_get_current_user();
    if ( $current_user->ID !== 0 ) {
        $first_name = get_user_meta( $current_user->ID, 'first_name', true );
        return !empty( $first_name ) ? $first_name : $current_user->user_login;
    }
    return '';
}

// Renders the 'current_user_first_name' tag by fetching the current user's first name or username.
add_filter( 'bricks/dynamic_data/render_tag', 'render_current_user_first_name_tag', 10, 3 );
function render_current_user_first_name_tag( $tag, $post, $context = 'text' ) {
    if ( $tag === 'current_user_first_name' ) {
        return get_current_user_first_name();
    }
    return $tag;
}

// Replaces the '{current_user_first_name}' placeholder in content with the current user's first name or username.
add_filter( 'bricks/dynamic_data/render_content', 'render_current_user_first_name_in_content', 10, 3 );
add_filter( 'bricks/frontend/render_data', 'render_current_user_first_name_in_content', 10, 2 );
function render_current_user_first_name_in_content( $content, $post, $context = 'text' ) {
    if ( strpos( $content, '{current_user_first_name}' ) !== false ) {
        $first_name = get_current_user_first_name();
        $content = str_replace( '{current_user_first_name}', $first_name, $content );
    }
    return $content;
}