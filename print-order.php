<?php
/**
 * Plugin Name: MMLS Print Order
 * Description: Adds "Print Invoice" and "Print Shipping" buttons to the WooCommerce order page.
 * Version: 1.0
 * Author: Win Htoo Shwe
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue plugin scripts and styles
// Enqueue plugin scripts and styles
function wc_print_buttons_enqueue_scripts( $hook ) {
    // Get the current screen object
    $current_screen = get_current_screen();

    // Log debugging information
    error_log( "Current Screen ID: " . $current_screen->id );

    // Check if we are on the WooCommerce orders page
    if ( $current_screen->id === 'woocommerce_page_wc-orders' || $current_screen->post_type === 'shop_order' ) {
        // Generate URLs
        $script_url = plugin_dir_url( __FILE__ ) . 'js/print-order.js';
        $style_url = plugin_dir_url( __FILE__ ) . 'css/print-order-style.css';

        // Debugging URLs
        error_log( "Script URL: " . $script_url );
        error_log( "Style URL: " . $style_url );

        // Enqueue scripts and styles
        wp_enqueue_script( 'wc-print-buttons-script', $script_url, array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'wc-print-buttons-style', $style_url );
    } else {
        error_log( "Not on the WooCommerce orders page. Screen ID: " . $current_screen->id );
    }
}

add_action( 'admin_enqueue_scripts', 'wc_print_buttons_enqueue_scripts' );

// Add a meta box with the buttons to the WooCommerce order page sidebar
function wc_print_buttons_add_meta_box() {
    $screen = get_current_screen();
    $screen_id = $screen ? $screen->id : '';

    // Log the screen ID to debug
    error_log( "Current screen ID: " . $screen_id );

    // Add the meta box for both environments
    if ( $screen_id === 'woocommerce_page_wc-orders' || $screen_id === 'shop_order' ) {
        add_meta_box(
            'wc-print-buttons-meta-box',
            'Print Actions',
            'wc_print_buttons_meta_box_content',
            $screen_id === 'shop_order' ? 'shop_order' : null, // Only pass post type for 'shop_order'
            'side',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'wc_print_buttons_add_meta_box' );

// Render the content of the meta box
function wc_print_buttons_meta_box_content() {
    echo '<div id="wc-print-buttons-sidebar" class="mmls-print-buttons">';
    echo '<button id="print-invoice" class="button woocommerce-button">Print Invoice</button>';
    echo '<button id="print-shipping" class="button woocommerce-button">Print Shipping</button>';
    echo '</div>';
}