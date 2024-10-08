<?php 

function ft_add_menu_page() {
    add_menu_page(
        'Flowtitude Settings', // Page title
        'FT Settings', // Menu title
        'manage_options', // Capability
        'ft-settings', // Menu slug
        'ft_settings_page_callback', // Function
        '', // Icon URL (optional)
        99 // Position (optional, set to a high number to make it the last item)
    );
}
add_action('admin_menu', 'ft_add_menu_page');

function ft_settings_page_callback() {
    ?>
    <div class="wrap">
        <h1>Flowtitude Bricks Builder Child Theme Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ft_settings_group');
            do_settings_sections('ft-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function ft_register_settings() {
    register_setting('ft_settings_group', 'ft_settings');

    add_settings_section(
        'ft_general_section',
        'Enable or Disable Settings Depending on Your Project Needs and Requirements',
        'ft_general_section_callback',
        'ft-settings'
    );
}
add_action('admin_init', 'ft_register_settings');

function ft_general_section_callback() {
    echo '<br><br>';
}



// Customizer Activator - Empty Setting
function mytheme_customize_register( $wp_customize ) {
    $wp_customize->add_setting( 'footer_custom_css', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'footer_custom_css', array(
        'label'       => ' ',
        'section'     => 'custom_css', 
        'settings'    => 'footer_custom_css',
        'type'        => 'checkbox',
        'description' => ' ',
    ) );
}
add_action( 'customize_register', 'mytheme_customize_register' );