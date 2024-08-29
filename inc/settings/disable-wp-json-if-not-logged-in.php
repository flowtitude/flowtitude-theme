<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ft_setup_json_disable_field() {
    add_settings_field(
        'ft_disable_json',
        'Disable JSON API for Guests',
        'ft_json_disable_callback',
        'ft-settings',
        'ft_general_section'
    );
}
add_action('admin_init', 'ft_setup_json_disable_field');

function ft_json_disable_callback() {
    $options = get_option('ft_settings');
    ?>
    <input type="checkbox" class="ft-switch" id="disable_json" name="ft_settings[disable_json]" value="1" <?php checked(isset($options['disable_json']), 1); ?>><label for="disable_json" class="ft-switch-label"></label>
    <p class="label">Enabling this setting will disable the JSON API (wp-json) for users who are not logged in.</p>
    <?php
}

add_filter('rest_authentication_errors', function($result) {
    if (!is_user_logged_in()) {
        $options = get_option('ft_settings');
        if (isset($options['disable_json']) && $options['disable_json']) {
            return new WP_Error('rest_not_logged_in', 'You are not logged in.', array('status' => 401));
        }
    }
    return $result; // Return the original result if the user is logged in or the setting is not enabled
});
