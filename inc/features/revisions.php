<?php
if (!defined('ABSPATH')) exit;

/**
 * Límite de revisiones basado en los ajustes del panel Flowtitude
 */
add_filter('wp_revisions_to_keep', function ($num, $post) {
	$options = get_option('flowtitude_settings');
	$limit = isset($options['revision_limit']) ? intval($options['revision_limit']) : 3;

	if (in_array(get_post_type($post), ['post', 'page', 'bricks_template'])) {
		return $limit;
	}

	return $num;
}, 1, 2);

/**
 * Elimina revisiones excedentes tras guardar post
 */
add_action('wp_insert_post', function ($post_id) {
	if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

	$options = get_option('flowtitude_settings');
	$limit = isset($options['revision_limit']) ? intval($options['revision_limit']) : 3;

	$revisions = wp_get_post_revisions($post_id, ['orderby' => 'post_date', 'order' => 'DESC']);
	if (count($revisions) > $limit) {
		foreach (array_slice($revisions, $limit) as $revision) {
			wp_delete_post($revision->ID, true);
		}
	}
});
