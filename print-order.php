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

// Main plugin function
function wc_print_buttons_enqueue_scripts() {
    // Enqueue your JavaScript file (to be created later)
    wp_enqueue_script( 'wc-print-buttons-script', plugin_dir_url( __FILE__ ) . 'js/print-order.js', array( 'jquery' ), '1.0', true );
    wp_enqueue_style( 'wc-print-buttons-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
}

add_action( 'admin_enqueue_scripts', 'wc_print_buttons_enqueue_scripts' );

// Add buttons to the WooCommerce order page
function wc_print_buttons_add_to_order_page() {
  global $post;

  // Ensure we're on a WooCommerce order page
  if ( get_post_type( $post ) !== 'shop_order' ) {
      return;
  }

  echo '<div id="wc-print-buttons-sidebar">';
  echo '<button id="print-invoice" class="button">Print Invoice</button>';
  echo '<button id="print-shipping" class="button">Print Shipping</button>';
  echo '</div>';
}

add_action( 'add_meta_boxes', 'wc_print_buttons_add_to_order_page' );