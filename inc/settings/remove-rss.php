<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function snn_remove_rss() {
  $options = get_option('ft_settings');
  if (isset($options['remove_rss'])) {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'wlwmanifest_link');
  }
}
add_action('init', 'snn_remove_rss');

function ft_remove_rss_setting_field() {
  add_settings_field(
    'ft_remove_rss',
    'Disable Remove RSS',
    'ft_remove_rss_callback',
    'ft-settings',
    'ft_general_section'
  );
}
add_action('admin_init', 'ft_remove_rss_setting_field');

function ft_remove_rss_callback() {
  $options = get_option('ft_settings');
  ?>
  <input type="checkbox"class="ft-switch" id="remove_rss" name="ft_settings[remove_rss]" value="1" <?php checked(isset($options['remove_rss']), 1); ?>><label for="remove_rss" class="ft-switch-label"></label>
  <p class="label">Enabling this setting will remove the RSS feed links from your website's HTML source code.</p>
  <?php
}