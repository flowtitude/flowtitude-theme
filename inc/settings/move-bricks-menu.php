<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ft_custom_menu_order($menu_ord) {
    $options = get_option('ft_settings');
    if (isset($options['move_bricks_menu']) && $options['move_bricks_menu']) {
        if (!$menu_ord) return true;
        foreach ($menu_ord as $index => $item) {
            if ($item == 'bricks') {
                $bricks_menu = $item;
                unset($menu_ord[$index]);
                break;
            }
        }
        if (isset($bricks_menu)) {
            $menu_ord[] = $bricks_menu;
        }
        return $menu_ord;
    }
    return $menu_ord;
}
add_filter('menu_order', 'ft_custom_menu_order');
add_filter('custom_menu_order', function () { return true; });

function ft_move_bricks_menu_setting_field() {
    add_settings_field(
        'ft_move_bricks_menu',
        'Move Bricks Menu to End',
        'ft_move_bricks_menu_callback',
        'ft-settings',
        'ft_general_section'
    );
}
add_action('admin_init', 'ft_move_bricks_menu_setting_field');

function ft_move_bricks_menu_callback() {
    $options = get_option('ft_settings');
    ?>
    <input type="checkbox" id="move_bricks_menu" name="ft_settings[move_bricks_menu]" value="1" class="ft-switch" <?php checked(isset($options['move_bricks_menu']), 1); ?>><label for="move_bricks_menu" class="ft-switch-label"></label>
    <p class="label">Enabling this setting will move the Bricks menu item to the end of the WordPress admin menu.</p>
    <?php
}