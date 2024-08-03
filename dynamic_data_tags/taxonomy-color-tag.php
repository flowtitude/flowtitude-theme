<?php 
// {taxonomy_color_tag:category}
// Taxonomy color custom field 
// The tag can be used with any taxonomy, e.g., {taxonomy_color_tag:category} or 
// {taxonomy_color_tag:custom_taxonomy_name}, to fetch the color.
// Adds the new tag 'taxonomy_color_tag' to the Bricks Builder dynamic tags list with dynamic term support.
add_filter( 'bricks/dynamic_tags_list', 'add_taxonomy_color_tag_to_builder' );
function add_taxonomy_color_tag_to_builder( $tags ) {
    $tags[] = [
        'name'  => '{taxonomy_color_tag}',
        'label' => 'Taxonomy Color Tag',
        'group' => 'Flowtitude',
    ];
    return $tags;
}

// Retrieves the custom field 'color' for any given taxonomy.
function get_taxonomy_color($post_id, $taxonomy) {
    $terms = get_the_terms($post_id, $taxonomy);
    if (!empty($terms) && !is_wp_error($terms)) {
        $term_id = $terms[0]->term_id; // Get the first term ID
        $color = get_term_meta($term_id, 'color', true);
        return $color ? $color : '#000000'; // Default to black if no color is set
    }
    return '#000000'; // Default to black if no terms found
}

// Renders the 'taxonomy_color_tag' tag by fetching the color for the specified taxonomy.
add_filter( 'bricks/dynamic_data/render_tag', 'render_taxonomy_color_tag', 10, 3 );
function render_taxonomy_color_tag( $tag, $post, $context = 'text' ) {
    if ( strpos($tag, 'taxonomy_color_tag:') === 0 ) {
        $taxonomy = explode(':', $tag)[1];
        return get_taxonomy_color($post->ID, $taxonomy);
    }
    return $tag;
}

// Applies dynamic taxonomy colors to render the color in content.
add_filter( 'bricks/dynamic_data/render_content', 'render_taxonomy_color_in_content', 10, 3 );
add_filter( 'bricks/frontend/render_data', 'render_taxonomy_color_in_content', 10, 2 );
function render_taxonomy_color_in_content( $content, $post, $context = 'text' ) {
    if ( preg_match_all('/{taxonomy_color_tag:([^}]+)}/', $content, $matches) ) {
        foreach ($matches[1] as $index => $taxonomy) {
            $color = get_taxonomy_color($post->ID, $taxonomy);
            $content = str_replace($matches[0][$index], $color, $content);
        }
    }
    return $content;
}