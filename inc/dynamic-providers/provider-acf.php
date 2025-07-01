<?php
class Flowtitude_Provider_ACF {
    public static function get_tag_value($tag, $context = []) {
        if (strpos($tag, 'acf.') === 0 && function_exists('get_field')) {
            $field = substr($tag, 4);
            $post = isset($context['post']) ? $context['post'] : null;
            return esc_html(get_field($field, $post));
        }
        return null;
    }
} 