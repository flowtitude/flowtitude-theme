<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ft_remove_wp_version() {
  $options = get_option('ft_settings');
  if (isset($options['remove_wp_version'])) {
    return '';
  }
}
add_filter('the_generator', 'ft_remove_wp_version');

function ft_remove_wp_version_setting_field() {
  add_settings_field(
    'ft_remove_wp_version',
    'Remove/Hide WP Version',
    'ft_remove_wp_version_callback',
    'ft-settings',
    'ft_general_section'
  );
}
add_action('admin_init', 'ft_remove_wp_version_setting_field');

function ft_remove_wp_version_callback() {
  $options = get_option('ft_settings');
  ?>
  <input type="checkbox" id="remove_wp_version" class="ft-switch" name="ft_settings[remove_wp_version]" value="1" <?php checked(isset($options['remove_wp_version']), 1); ?>><label for="remove_wp_version" class="ft-switch-label" ></label>
  <p class="label">Enabling this setting will remove the WordPress version number from your website's HTML source code.</p>
  <?php
}