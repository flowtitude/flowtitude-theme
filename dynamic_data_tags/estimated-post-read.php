<?php 
// {estimated_post_read_time}
// Adds a new dynamic tag 'estimated_post_read_time' to Bricks Builder for displaying estimated post read time.
add_filter( 'bricks/dynamic_tags_list', 'add_estimated_post_read_time_tag_to_builder' );

function add_estimated_post_read_time_tag_to_builder( $tags ) {
    $tags[] = [
        'name'  => '{estimated_post_read_time}',
        'label' => 'Estimated Post Read Time',
        'group' => 'Flowtitude',
    ];

    return $tags;
}

// Calculates the estimated read time based on word count. Assumes an average reading speed of 200 words per minute.
function calculate_estimated_read_time() {
    global $post;
    $word_count = str_word_count( strip_tags( $post->post_content ) );
    $read_time = ceil( $word_count / 200 ); // Average reading speed: 200 words per minute
    return $read_time;
}

// Renders the 'estimated_post_read_time' tag by fetching the estimated read time.
add_filter( 'bricks/dynamic_data/render_tag', 'render_estimated_post_read_time_tag', 10, 3 );
function render_estimated_post_read_time_tag( $tag, $post, $context = 'text' ) {
    if ( $tag === 'estimated_post_read_time' ) {
        return calculate_estimated_read_time();
    }
    return $tag;
}

// Replaces the '{estimated_post_read_time}' placeholder in content with the actual estimated read time.
add_filter( 'bricks/dynamic_data/render_content', 'render_estimated_post_read_time_in_content', 10, 3 );
add_filter( 'bricks/frontend/render_data', 'render_estimated_post_read_time_in_content', 10, 2 );
function render_estimated_post_read_time_in_content( $content, $post, $context = 'text' ) {
    if ( strpos( $content, '{estimated_post_read_time}' ) !== false ) {
        $read_time = calculate_estimated_read_time();
        $content = str_replace( '{estimated_post_read_time}', $read_time, $content );
    }
    return $content;
}