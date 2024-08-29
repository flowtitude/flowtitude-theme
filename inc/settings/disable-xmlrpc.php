<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ft_disable_xmlrpc($enabled) {
  $options = get_option('ft_settings');
  if (isset($options['disable_xmlrpc'])) {
    return false;
  }
  return $enabled;
}
add_filter('xmlrpc_enabled', 'ft_disable_xmlrpc');

function ft_disable_xmlrpc_setting_field() {
  add_settings_field(
    'ft_disable_xmlrpc',
    'Disable XML-RPC',
    'ft_disable_xmlrpc_callback',
    'ft-settings',
    'ft_general_section'
  );
}
add_action('admin_init', 'ft_disable_xmlrpc_setting_field');

function ft_disable_xmlrpc_callback() {
  $options = get_option('ft_settings');
  ?>
  <input type="checkbox" id="disable_xmlrpc" class="ft-switch" name="ft_settings[disable_xmlrpc]" value="1" <?php checked(isset($options['disable_xmlrpc']), 1); ?>><label for="disable_xmlrpc" class="ft-switch-label"></label>
  <p class="label">Enabling this setting will disable the XML-RPC functionality in WordPress.</p>
  <?php
}