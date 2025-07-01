<?php
class Flowtitude_Provider_User {
    public static function get_tag_value($tag, $context = []) {
        $user = isset($context['user']) ? $context['user'] : wp_get_current_user();
        switch ($tag) {
            case 'wp_user_nickname':
                return esc_html($user->nickname);
            case 'wp_user_avatar':
                return esc_url(get_avatar_url($user->ID));
        }
        return null;
    }
} 