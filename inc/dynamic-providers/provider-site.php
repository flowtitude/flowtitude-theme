<?php
class Flowtitude_Provider_Site {
    public static function get_tag_value($tag, $context = []) {
        switch ($tag) {
            case 'site_title':
                return esc_html(get_bloginfo('name'));
        }
        return null;
    }
} 