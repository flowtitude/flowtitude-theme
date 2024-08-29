<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ft_disable_file_edit($enabled) {
  $options = get_option('ft_settings');
  if (isset($options['disable_file_edit'])) {
    define('DISALLOW_FILE_EDIT', true);
    return false;
  }
  return $enabled;
}
add_filter('admin_init', 'ft_disable_file_edit');

function ft_disable_file_edit_setting_field() {
  add_settings_field(
    'ft_disable_file_edit',
    'Disable File Editing',
    'ft_disable_file_edit_callback',
    'ft-settings',
    'ft_general_section'
  );
}
add_action('admin_init', 'ft_disable_file_edit_setting_field');

function ft_disable_file_edit_callback() {
  $options = get_option('ft_settings');
  ?>
  <input type="checkbox" id="disable_file_edit" class="ft-switch" name="ft_settings[disable_file_edit]" value="1" <?php checked(isset($options['disable_file_edit']), 1); ?>><label for="disable_file_edit" class="ft-switch-label"></label>
  <p class="label">Enabling this setting will disable file editing from the WordPress dashboard.</p>
  <?php
}
