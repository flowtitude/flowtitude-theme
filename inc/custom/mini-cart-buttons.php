<?php 

add_action( 'woocommerce_widget_shopping_cart_buttons', function(){
    // Removing Buttons
    remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
    remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

    // Adding customized Buttons
    add_action( 'woocommerce_widget_shopping_cart_buttons', 'custom_widget_shopping_cart_button_view_cart', 10 );
    add_action( 'woocommerce_widget_shopping_cart_buttons', 'custom_widget_shopping_cart_proceed_to_checkout', 20 );
}, 1 );

// Custom cart button
function custom_widget_shopping_cart_button_view_cart() {
    $original_link = wc_get_cart_url();
    $custom_link = home_url( '/cart/?id=1' ); // HERE replacing cart link
    echo '<a href="' . esc_url( $custom_link ) . '" class="btn--outline">' . esc_html__( 'View cart', 'woocommerce' ) . '</a>';
}

// Custom Checkout button
function custom_widget_shopping_cart_proceed_to_checkout() {
    $original_link = wc_get_checkout_url();
    $custom_link = home_url( '/checkout/?id=1' ); // HERE replacing checkout link
    echo '<a href="' . esc_url( $custom_link ) . '" class="btn">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a>';
}