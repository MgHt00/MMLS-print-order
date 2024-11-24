<?php
/**
 * Plugin Name: MMLS Print Order
 * Description: Adds "Print Invoice" and "Print Shipping" buttons to the WooCommerce order page.
 * Version: 1.1
 * Author: Win Htoo Shwe
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue plugin scripts and styles
function wc_print_buttons_enqueue_scripts( $hook ) {
    $current_screen = get_current_screen();

    // Check if we are on the WooCommerce orders page or a specific order page
    if ( $current_screen->id === 'woocommerce_page_wc-orders' || $current_screen->post_type === 'shop_order' ) {
        // Enqueue JavaScript and CSS files
        wp_enqueue_script(
            'wc-print-buttons-script',
            plugin_dir_url( __FILE__ ) . 'js/print-order.js',
            array( 'jquery' ),
            '1.1',
            true
        );
        wp_enqueue_style(
            'wc-print-buttons-style',
            plugin_dir_url( __FILE__ ) . 'css/print-order-style.css'
        );

        // Pass ajaxurl to JavaScript
        wp_localize_script( 'wc-print-buttons-script', 'ajax_object', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'wc_print_buttons_enqueue_scripts' );

// Add a meta box to the WooCommerce order page sidebar
function wc_print_buttons_add_meta_box() {
    $screen = get_current_screen();
    $screen_id = $screen ? $screen->id : '';

    // Add the meta box for orders
    if ( $screen_id === 'woocommerce_page_wc-orders' || $screen_id === 'shop_order' ) {
        add_meta_box(
            'wc-print-buttons-meta-box',
            'Print Orders',
            'wc_print_buttons_meta_box_content',
            $screen_id === 'shop_order' ? 'shop_order' : null, // Use 'shop_order' post type
            'side',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'wc_print_buttons_add_meta_box' );

// Render the meta box content
function wc_print_buttons_meta_box_content() {
    global $post;

    // Check if this is the "Add New Order" page
    $current_url = $_SERVER['REQUEST_URI'];
    $is_new_order_page = strpos( $current_url, 'post-new.php?post_type=shop_order' ) !== false;

    if ( $is_new_order_page ) {
        echo '<p>This section is not available for new orders.</p>';
        return;
    }

    // Determine the order ID
    $order_id = isset($_GET['id']) ? intval($_GET['id']) : ( isset($post->ID) ? $post->ID : 0 );

    if ( $order_id > 0 ) {
        // Fetch the WooCommerce order object
        $order = wc_get_order( $order_id );

        if ( $order ) {
            // Display buttons
            echo '<div id="wc-print-buttons-sidebar" class="mmls-print-buttons">';
            echo '<button id="print-invoice" class="button woocommerce-button" data-order-id="' . esc_attr( $order_id ) . '">Print Invoice</button>';
            echo '<button id="print-shipping" class="button woocommerce-button">Print Shipping</button>';
            echo '</div>';
            //echo wc_print_order_info($order);
        } else {
            echo '<p>Order data could not be fetched.</p>';
        }
    } else {
        echo '<p>This section is not available for new orders.</p>';
    }
}

// Helper function to render order info (currently unused)
function wc_print_order_info( $order ) {
    return '<div id="order-info-sidebar" class="mmls-order-info">
        <h3>Order Information</h3>
        <p><strong>Order ID:</strong> ' . esc_html( $order->get_id() ) . '</p>
        <p><strong>Status:</strong> ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</p>
        <p><strong>Total:</strong> ' . esc_html( $order->get_total() ) . ' ' . esc_html( $order->get_currency() ) . '</p>
        <p><strong>Customer Name:</strong> ' . esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) . '</p>
        <p><strong>Billing Email:</strong> ' . esc_html( $order->get_billing_email() ) . '</p>
        <p><strong>Billing Address:</strong> ' . esc_html( $order->get_formatted_billing_address() ) . '</p>
    </div>';
}

// Handle test AJAX action
function handle_test_ajax_action() {
    // Return a response to check if the request is successful
    wp_send_json_success('Test AJAX action successful!');
}
add_action( 'wp_ajax_test_ajax_action', 'handle_test_ajax_action' );
add_action( 'wp_ajax_nopriv_test_ajax_action', 'handle_test_ajax_action' ); // For non-logged-in users
