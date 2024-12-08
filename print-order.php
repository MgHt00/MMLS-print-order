<?php
/**
 * Plugin Name: MMLS Print Order
 * Description: Enhances WooCommerce by adding customizable "Print Invoice" and "Print Shipping" buttons directly to the order management page. Generate clean, professional invoices and shipping labels with order details and company branding, ready for printing.
 * Version: 1.0
 * Author: Win Htoo Shwe, OpenAI's ChatGPT (Code Assistance)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue plugin scripts and styles
function wc_print_buttons_enqueue_scripts( $hook ) {
    $current_screen = get_current_screen();
    $company_details = include plugin_dir_path(__FILE__) . 'company-details.php';

    // Check if we are on the WooCommerce orders page or a specific order page
    if ( $current_screen->id === 'woocommerce_page_wc-orders' || $current_screen->post_type === 'shop_order' ) {
        // Enqueue jQuery
        wp_enqueue_script('jquery');

        // Enqueue custom JS file
        wp_enqueue_script(
            'wc-print-buttons-script',
            plugin_dir_url(__FILE__) . 'js/print-order.js',
            array('jquery'),  // Ensure jQuery is loaded first
            '1.1',
            true
        );

        // Enqueue CSS (optional)
        wp_enqueue_style(
            'wc-print-buttons-style',
            plugin_dir_url(__FILE__) . 'css/print-order-style.css'
        );

        // Pass ajaxurl and additional data to JavaScript
        wp_localize_script('wc-print-buttons-script', 'ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'plugin_url' => untrailingslashit(plugin_dir_url(__FILE__)), // Ensure no trailing slash
            'comp_name' => $company_details['comp_name'],
            'comp_address' => $company_details['comp_address'], 
            'thankyou_message' => $company_details['thankyou_message'],
            'comp_phone_number' => $company_details['comp_phone_number'],
            'comp_email_address' => $company_details['comp_email_address'],
        ));
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

    // Check if this is the "Add New Order" page [LE1]
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
            echo '<button id="print-invoice" type="button" class="button woocommerce-button" data-order-id="' . esc_attr( $order_id ) . '">Print Invoice</button>';
            echo '<button id="print-shipping" type="button" class="button woocommerce-button" data-order-id="' . esc_attr( $order_id ) . '">Print Shipping</button>';
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

// Helper function to returns the required data (shipping address, phone number, etc.)
function get_order_details($order) {
    if (!$order instanceof WC_Order) {
        return false; // Ensure the input is a valid order object
    }

    // Fetch shipping address
    $shipping_address = $order->get_formatted_shipping_address();
    if ($shipping_address) {        
        // Normalize line breaks to a consistent <br />
        $normalized_address = str_replace(array('<br>', '<br/>', "\n"), '<br />', $shipping_address);

        // Log the normalized address for debugging
        error_log('Normalized Shipping Address: ' . $normalized_address);

        // Split at the first <br /> to separate name and remaining address
        $address_parts = explode('<br />', $normalized_address, 2);
        // splits the string at the first <br /> tag. ...
        // ... The 2 parameter ensures the split happens only once, creating an array with two parts:    
        // ... $address_parts[0] contains the name.
        // ... $address_parts[1] contains the rest of the address.// Normalize line breaks to a consistent <br />

        // Extract shipping name (first part)
        $shipping_name = isset($address_parts[0]) ? trim($address_parts[0]) : '';

        // Extract the remaining address
        $shipping_address_only = isset($address_parts[1]) ? trim($address_parts[1]) : '';
    } else {
        $shipping_address = '';
        $shipping_name = '';
        $shipping_address_only = '';
    }

    // Fetch billing phone number
    $phone_number = $order->get_billing_phone();
    if (!$phone_number) {
        $phone_number = '';
    }

    // Fetch order date
    $order_date = $order->get_date_created();
    if ($order_date) {
        $order_date = $order_date->date('F j, Y'); // Format the date
    } else {
        $order_date = '';
    }

    // Fetch payment method
    $payment_method = $order->get_payment_method(); // Internal payment method key (e.g., 'cod', 'paypal')
    $payment_method_title = $order->get_payment_method_title(); // Human-readable title (e.g., 'Cash on Delivery', 'PayPal')

    // Fetch customer note
    $customer_note = $order->get_customer_note();
    if (!$customer_note) {
        $customer_note = ''; // Default to empty if no note exists
    }

    return array(
        'shipping_address' => $shipping_address,
        'shipping_name' => $shipping_name,
        'shipping_address_only' => $shipping_address_only,
        'phone_number'     => $phone_number,
        'order_date'       => $order_date,
        'payment_method'      => $payment_method,
        'payment_method_title' => $payment_method_title,
        'customer_note'    => $customer_note, 
    );
}


// Handle AJAX request to generate the invoice
function handle_generate_invoice() {
    // Check for required data (order ID)
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        error_log('generate_invoice called');
        error_log('Order ID: ' . $order_id);

        // Get the WooCommerce order object
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(array('message' => 'Order not found.'));
            return;
        }

        // Reuse the helper function to get order details
        $order_details = get_order_details($order);

        $shipping_address = $order_details['shipping_address'];
        $phone_number = $order_details['phone_number'];
        $order_date = $order_details['order_date'];

        $subtotal = 0;


        // Start generating the invoice content
        $invoice_content .= '<table><tr><th>Item</th><th>SKU</th><th>Price</th><th>Quantity</th><th>Total</th></tr>';

        // Loop through the order items and display their details
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_name = esc_html($item->get_name());
            $item_sku = $product ? esc_html($product->get_sku()) : 'N/A';
            $item_price = wc_price($item->get_total() / $item->get_quantity()); // Price per item
            $item_quantity = esc_html($item->get_quantity());
            $item_total = wc_price($item->get_total());

            // Add row for each item
            $invoice_content .= '<tr>';
            $invoice_content .= '<td>' . $item_name . '</td>';
            $invoice_content .= '<td>' . $item_sku . '</td>';
            $invoice_content .= '<td>' . $item_price . '</td>';
            $invoice_content .= '<td>' . $item_quantity . '</td>';
            $invoice_content .= '<td>' . $item_total . '</td>';
            $invoice_content .= '</tr>';
            
            // Accumulate the subtotal
            $subtotal += $item->get_total(); // Add the item's total (excluding tax)
        }

        // Add the subtotal row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right">Sub Total</td>';
        $invoice_content .= '<td>' . wc_price($subtotal) . '</td>';
        $invoice_content .= '</tr>';

        // Get the shipping total
        $shipping_total = $order->get_shipping_total();

        // Add the shipping row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right">Shipping</td>';
        $invoice_content .= '<td>' . wc_price($shipping_total) . '</td>';
        $invoice_content .= '</tr>';
        
        // Add the total row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right"><strong>Total</strong></td>';
        $invoice_content .= '<td><strong>' . wc_price($order->get_total()) . '</strong></td>';
        $invoice_content .= '</tr>';

        // Close the table
        $invoice_content .= '</table>';

        // Prepare the response
        $response = array(
            'success' => true,
            'invoice' => $invoice_content,
            'order_id' => $order_id,
            'shipping_address' => $shipping_address,
            'phone_number' => $phone_number,
            'order_date' => $order_date,
        );

        // Send the response as JSON
        wp_send_json_success($response);
    } else {
        // If no order ID is found
        wp_send_json_error(array('message' => 'Order ID not found.'));
    }
}

add_action('wp_ajax_generate_invoice', 'handle_generate_invoice');

// Handle AJAX request to generate the shipping label
function handle_generate_shipping() {
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);

        error_log('generate_shipping called');
        error_log('Order ID: ' . $order_id);

        // Get the WooCommerce order object
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(array('message' => 'Order not found.'));
            return;
        }

        // Reuse the helper function to get order details
        $order_details = get_order_details($order);

        $shipping_address = $order_details['shipping_address'];
        $shipping_name = $order_details['shipping_name'];
        $shipping_address_only = $order_details['shipping_address_only'];
        $phone_number = $order_details['phone_number'];
        $order_date = $order_details['order_date'];
        $payment_method = $order_details['payment_method_title'];
        $customer_note = $order_details['customer_note'];

        // Get the total amount with comma separation
        $total_amount = number_format($order->get_total(), 0); // Formats the amount with two decimal places and commas

        // Prepare the response
        $response = array(
            'success' => true,
            'order_id' => $order_id,
            'shipping_address' => $shipping_address,
            'shipping_name' => $shipping_name,
            'shipping_address_only' => $shipping_address_only,
            'phone_number' => $phone_number,
            /*'order_date' => $order_date,*/
            'payment_method' => $payment_method,
            'customer_note' => $customer_note,
            'total_amount' => $total_amount,
        );

        // Send the response as JSON
        wp_send_json_success($response);
    } else {
        // If no order ID is found
        wp_send_json_error(array('message' => 'Order ID not found.'));
    }
}
add_action('wp_ajax_generate_shipping', 'handle_generate_shipping');