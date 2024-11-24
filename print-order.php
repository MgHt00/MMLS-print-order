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
            'Print Orders',
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
    global $post;

    // Determine if we are on an existing order
    $order_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($post->ID) ? $post->ID : 0);
    $is_edit_page = $order_id > 0; // True if the order ID is valid

    if ($is_edit_page) {
        // Get the WooCommerce order object
        $order = wc_get_order($order_id);

        if ($order) {
            // Display buttons
            echo '<div id="wc-print-buttons-sidebar" class="mmls-print-buttons">';
            echo '<button id="print-invoice" class="button woocommerce-button">Print Invoice</button>';
            echo '<button id="print-shipping" class="button woocommerce-button">Print Shipping</button>';
            echo '</div>';

            // Display order info
            // echo wc_print_order_info($order);
        } else {
            // Handle case where order is invalid
            echo '<p>Order data could not be fetched.</p>';
        }
    } else {
        // Do not display anything for the "new order" page
        echo '<p>This section is not available for new orders.</p>';
    }
}

function wc_print_order_info($order) {
    return '<div id="order-info-sidebar" class="mmls-order-info">
        <h3>Order Information</h3>
        <p><strong>Order ID:</strong> ' . $order->get_id() . '</p>
        <p><strong>Status:</strong> ' . wc_get_order_status_name( $order->get_status() ) . '</p>
        <p><strong>Total:</strong> ' . $order->get_total() . ' ' . $order->get_currency() . '</p>
        <p><strong>Customer Name:</strong> ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</p>
        <p><strong>Billing Email:</strong> ' . $order->get_billing_email() . '</p>
        <p><strong>Billing Address:</strong> ' . $order->get_formatted_billing_address() . '</p>
    </div>';
}
