<?php 
// {taxonomy_term_slug:taxonomy}
// Use {taxonomy_term_slug:category} to insert slugs of categories assigned to the post.
// Adds a new dynamic tag 'taxonomy_term_slug' to the Bricks Builder tags list.
add_filter( 'bricks/dynamic_tags_list', 'add_taxonomy_term_slug_tag_to_builder' );
function add_taxonomy_term_slug_tag_to_builder( $tags ) {
    $tags[] = [
        'name'  => '{taxonomy_term_slug:category}',
        'label' => 'Taxonomy Term Slug',
        'group' => 'Flowtitude',
    ];

    return $tags;
}

// Retrieves the slugs of taxonomy terms associated with a post for a specific taxonomy.
function get_taxonomy_term_slug( $post, $taxonomy ) {
    if ( $post && isset( $post->ID ) && !empty($taxonomy) ) {
        $terms = get_the_terms( $post->ID, $taxonomy );
        if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
            $term_slugs = array_map(function($term) {
                return $term->slug;
            }, $terms);
            return implode(' ', $term_slugs);
        }
    }
    return '';
}

// Renders the 'taxonomy_term_slug' tag by fetching the taxonomy term slugs based on the specified taxonomy.
add_filter( 'bricks/dynamic_data/render_tag', 'render_taxonomy_term_slug_tag', 10, 3 );
function render_taxonomy_term_slug_tag( $tag, $post, $context = 'text' ) {
    if ( strpos($tag, 'taxonomy_term_slug') === 0 ) {
        $parts = explode(':', $tag);
        $taxonomy = isset($parts[1]) ? $parts[1] : 'category'; // Default to 'category' if not specified.
        return get_taxonomy_term_slug( $post, $taxonomy );
    }
    return $tag;
}

// Replaces placeholders like '{taxonomy_term_slug:taxonomy}' in content with actual taxonomy term slugs.
add_filter( 'bricks/dynamic_data/render_content', 'render_taxonomy_term_slug_in_content', 10, 3 );
add_filter( 'bricks/frontend/render_data', 'render_taxonomy_term_slug_in_content', 10, 2 );
function render_taxonomy_term_slug_in_content( $content, $post, $context = 'text' ) {
    if ( preg_match_all('/\{taxonomy_term_slug:([a-zA-Z0-9_\-]+)\}/', $content, $matches, PREG_SET_ORDER) ) {
        foreach ($matches as $match) {
            $taxonomy = $match[1];
            $slug = get_taxonomy_term_slug( $post, $taxonomy );
            $content = str_replace($match[0], $slug, $content);
        }
    }
    return $content;
}