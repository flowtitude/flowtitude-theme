<?php 
// {post_count:post_type_name}  
// {post_count:post_type_name:taxonomy_name:term_slug}
// Get post count for post type or post type with taxonomy count
// Adds a new tag 'post_count' to the Bricks Builder dynamic tags list with support for any post type and taxonomy.
add_filter('bricks/dynamic_tags_list', 'add_post_count_tag_to_builder');
function add_post_count_tag_to_builder($tags) {
    $tags[] = [
        'name'  => '{post_count:post_type_name}',
        'label' => 'Post Count',
        'group' => 'Flowtitude',
    ];
    return $tags;
}

// Retrieves the total count of a specified post type, optionally filtered by a taxonomy term.
function get_post_count($post_type, $taxonomy = '', $term_slug = '') {
    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'fields'         => 'ids'
    ];
    if (!empty($taxonomy) && !empty($term_slug)) {
        $args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $term_slug,
            ],
        ];
    }
    $query = new WP_Query($args);
    return $query->found_posts;
}

// Renders the 'post_count' tag by fetching the count dynamically.
add_filter('bricks/dynamic_data/render_tag', 'render_post_count_tag', 10, 3);
function render_post_count_tag($tag, $post, $context = 'text') {
    if (strpos($tag, 'post_count:') === 0) {
        $parts = explode(':', $tag);
        $post_type = $parts[1] ?? '';
        $taxonomy = $parts[2] ?? '';
        $term_slug = $parts[3] ?? '';
        return get_post_count($post_type, $taxonomy, $term_slug);
    }
    return $tag;
}

// Applies dynamic tags to render the post count in content.
add_filter('bricks/dynamic_data/render_content', 'render_post_count_in_content', 10, 3);
add_filter('bricks/frontend/render_data', 'render_post_count_in_content', 10, 2);
function render_post_count_in_content($content, $post, $context = 'text') {
    if (preg_match_all('/{post_count:([\w-]+)(?::([\w-]+):([\w-]+))?}/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $post_type = $match[1];
            $taxonomy = $match[2] ?? '';
            $term_slug = $match[3] ?? '';
            $post_count = get_post_count($post_type, $taxonomy, $term_slug);
            $content = str_replace($match[0], $post_count, $content);
        }
    }
    return $content;
}