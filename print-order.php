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
function wc_print_buttons_enqueue_scripts($hook) {
    global $post;

    // Load only on WooCommerce order edit page
    if ( 'post.php' === $hook && 'shop_order' === get_post_type($post) ) {
        wp_enqueue_script( 'wc-print-buttons-script', plugin_dir_url( __FILE__ ) . 'js/print-order.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'wc-print-buttons-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'wc_print_buttons_enqueue_scripts' );

// Add a meta box with the buttons to the WooCommerce order page sidebar
function wc_print_buttons_add_meta_box() {
    add_meta_box(
        'wc-print-buttons-meta-box',            // Unique ID for the meta box
        'Print Actions',                        // Meta box title
        'wc_print_buttons_meta_box_content',    // Callback to render content
        'shop_order',                           // Post type (WooCommerce orders)
        'side',                                 // Context (display in sidebar)
        'default'                               // Priority
    );
}
add_action( 'add_meta_boxes', 'wc_print_buttons_add_meta_box' );

// Render the content of the meta box
function wc_print_buttons_meta_box_content() {
    echo '<div id="wc-print-buttons-sidebar" class="mmls-print-buttons">';
    echo '<button id="print-invoice" class="button woocommerce-button">Print Invoice</button>';
    echo '<button id="print-shipping" class="button woocommerce-button">Print Shipping</button>';
    echo '</div>';
}