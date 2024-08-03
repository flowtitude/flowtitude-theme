<?php 
// {current_author_id}
// Adds a new dynamic tag 'current_author_id' to Bricks Builder for displaying current author ID.
// checks if the current page is an author archive page using is_author() and retrieves the author ID 
// with get_queried_object_id() if it is. Otherwise, it returns an empty string.
add_filter( 'bricks/dynamic_tags_list', 'add_current_author_id_tag_to_builder' );

function add_current_author_id_tag_to_builder( $tags ) {
    $tags[] = [
        'name'  => '{current_author_id}',
        'label' => 'Current Author ID',
        'group' => 'Flowtitude',
    ];

    return $tags;
}

// Retrieves the current author ID on an author archive page.
function get_current_author_id() {
    return is_author() ? get_queried_object_id() : '';
}

// Renders the 'current_author_id' tag by fetching the current author ID.
add_filter( 'bricks/dynamic_data/render_tag', 'render_current_author_id_tag', 10, 3 );
function render_current_author_id_tag( $tag, $post, $context = 'text' ) {
    if ( $tag === 'current_author_id' ) {
        return get_current_author_id();
    }
    return $tag;
}

// Replaces the '{current_author_id}' placeholder in content with the actual current author ID.
add_filter( 'bricks/dynamic_data/render_content', 'render_current_author_id_in_content', 10, 3 );
add_filter( 'bricks/frontend/render_data', 'render_current_author_id_in_content', 10, 2 );
function render_current_author_id_in_content( $content, $post, $context = 'text' ) {
    if ( strpos( $content, '{current_author_id}' ) !== false ) {
        $author_id = get_current_author_id();
        $content = str_replace( '{current_author_id}', $author_id, $content );
    }
    return $content;
}