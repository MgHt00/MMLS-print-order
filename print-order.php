<?php
/**
 * Plugin Name: MMLS Print Order
 * Description: Enhances WooCommerce by adding customizable "Print Invoice" and "Print Shipping" buttons directly to the order management page. Generate clean, professional invoices and shipping labels with order details and company branding, ready for printing.
 * Version: 1.2
 * Author: Win Htoo Shwe, OpenAI's ChatGPT (Code Assistance)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// To include the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

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
            'ajaxurl'           => admin_url('admin-ajax.php'),
            'plugin_url'        => untrailingslashit(plugin_dir_url(__FILE__)),
            'comp_name'         => $company_details['comp_name'],
            'comp_address'      => $company_details['comp_address'],
            'thankyou_message'  => $company_details['thankyou_message'],
            'comp_phone_number' => $company_details['comp_phone_number'],
            'comp_email_address'=> $company_details['comp_email_address'],
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
    /*$current_url = $_SERVER['REQUEST_URI'];
    $is_new_order_page = strpos( $current_url, 'post-new.php?post_type=shop_order' ) !== false;*/
    $current_screen = get_current_screen();
    $is_new_order_page = $current_screen && $current_screen->base === 'shop_order' && $current_screen->action === 'add';

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
        } else {
            echo '<p>Order data could not be fetched.</p>';
        }
    } else {
        echo '<p>This section is not available for new orders.</p>';
    }
}

// Helper function to returns the required data (shipping address, phone number, etc.)
function get_order_details($order) {
    if (!$order instanceof WC_Order) {
        return false; // Ensure the input is a valid order object
    }

    // Fetch shipping address
    $shipping_address = $order->get_formatted_shipping_address();
    if ($shipping_address) {        
        // Normalize line breaks to <br />
        $normalized_address = str_replace(array('<br>', '<br/>', "\n"), '<br />', $shipping_address);

        // Log the normalized address for debugging
        error_log('Normalized Shipping Address: ' . $normalized_address);

        // Split at the first <br /> to separate name and remaining address
        $address_parts = explode('<br />', $normalized_address, 2);

        // Extract shipping name
        $shipping_name = isset($address_parts[0]) ? trim($address_parts[0]) : '';

        // Extract remaining address lines
        $remaining_address = isset($address_parts[1]) ? $address_parts[1] : '';

        // Split remaining address by <br />
        $remaining_parts = explode('<br />', $remaining_address);

        // Assign values based on the remaining parts
        $shipping_address_line = isset($remaining_parts[0]) ? trim($remaining_parts[0]) : '';
        $city = isset($remaining_parts[1]) ? trim($remaining_parts[1]) : '';
        $state_zip = isset($remaining_parts[2]) ? trim($remaining_parts[2]) : '';
    } else {
        $shipping_address = '';
        $shipping_name = '';
        $shipping_address_line = '';
        $city_state_zip = '';
    }
    
    // Log extracted parts for debugging
    error_log('Shipping Name: ' . $shipping_name);
    error_log('Shipping Address Line: ' . $shipping_address_line);
    error_log('City: ' . $city);
    error_log('State/Zip: ' . $state_zip);

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
        'shipping_address_line' => $shipping_address_line,
        'shipping_city' => $city,
        'shipping_state_zip' => $state_zip,
        'phone_number'     => $phone_number,
        'order_date'       => $order_date,
        'payment_method'      => $payment_method,
        'payment_method_title' => $payment_method_title,
        'customer_note'    => $customer_note, 
    );
}

// Helper function to generate barcode dynamically and output it directly as a data URI
function get_order_barcode($order_id) {
    try {
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($order_id, $generator::TYPE_CODE_128);

        // Check if barcode generation returned valid data
        if (!$barcode) {
            throw new Exception('Failed to generate barcode.');
        }

        // Encode the barcode as a Base64 string
        $barcode_base64 = base64_encode($barcode);

        // Return the Base64 image
        return 'data:image/png;base64,' . $barcode_base64;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log('Barcode generation error: ' . $e->getMessage());

        // Return a placeholder image or an error message
        return 'data:image/png;base64,' . base64_encode(''); // Return an empty PNG placeholder
    }
}

// Handle AJAX request to generate the invoice
function handle_generate_invoice() {
    // Check for required data (order ID)
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;

    if ($order_id > 0) {
        /*if (WP_DEBUG) {
            error_log('generate_shipping called');
            error_log('Order ID: ' . $order_id);
        }*/

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

        // Generate barcode with the helper function
        $barcode_data_uri = get_order_barcode($order_id);
        $order_number_barcode_URL = esc_attr($barcode_data_uri);

        $original_subtotal = 0;
        $subtotal = 0;
        $total_discount = 0;

        // Initialize the invoice content
        $invoice_content = '<table><tr><th>Item</th><th>SKU</th><th>Price</th><th>Quantity</th><th>Total</th></tr>';

        // Loop through the order items and display their details
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_name = esc_html($item->get_name());
            $item_sku = $product ? esc_html($product->get_sku()) : 'N/A';

            // Original price per item (regular price from product)
            $original_price = $product ? floatval($product->get_regular_price()) : 0;
            
            // Discounted total price (total amount for this item in the order)
            $item_total = $item->get_total();
            
            // Quantity of the item
            $item_quantity = intval($item->get_quantity());

            // Add row for each item
            $invoice_content .= '<tr>';
            $invoice_content .= '<td>' . $item_name . '</td>';
            $invoice_content .= '<td>' . $item_sku . '</td>';
            $invoice_content .= '<td>' . wc_price($original_price) . '</td>';
            $invoice_content .= '<td>' . $item_quantity . '</td>';
            $invoice_content .= '<td class="stick-to-right">' . wc_price($item_total) . '</td>';
            $invoice_content .= '</tr>';
            
            // Calculate subtotals
            $subtotal += $item_total;                      // Total after discounts
            $original_subtotal += $original_price * $item_quantity; // Total before discounts
        }

        // Calculate the total discount
        $total_discount = $original_subtotal - $subtotal;

        // Add the subtotal row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right">Sub Total</td>';
        $invoice_content .= '<td class="stick-to-right">' . wc_price($original_subtotal) . '</td>';
        $invoice_content .= '</tr>';

        // Add the discount row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right">Discount</td>';
        $invoice_content .= '<td class="stick-to-right">' . wc_price($total_discount) . '</td>';
        $invoice_content .= '</tr>';

        // Get the shipping total
        $shipping_total = $order->get_shipping_total();

        // Add the shipping row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right">Shipping</td>';
        $invoice_content .= '<td class="stick-to-right">' . wc_price($shipping_total) . '</td>';
        $invoice_content .= '</tr>';
        
        // Add the total row
        $invoice_content .= '<tr>';
        $invoice_content .= '<td colspan="4" class="stick-to-right"><strong>Total</strong></td>';
        $invoice_content .= '<td class="stick-to-right"><strong>' . wc_price($order->get_total()) . '</strong></td>';
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
            'order_number_barcode_URL' => $order_number_barcode_URL,
        );

        // Send the response as JSON
        wp_send_json_success($response);
    } else {
        // If no order ID is found
        wp_send_json_error(array('message' => 'Order ID not found.'));
        wp_die();
    }
}
add_action('wp_ajax_generate_invoice', 'handle_generate_invoice');

// Handle AJAX request to generate the shipping label
function handle_generate_shipping() {
    // Check for required data (order ID)
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;

    if ($order_id > 0) {
        /*if (WP_DEBUG) {
            error_log('generate_shipping called');
            error_log('Order ID: ' . $order_id);
        }*/

        // Get the WooCommerce order object
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(array('message' => 'Order not found.'));
            return;
        }

        // Reuse the helper function to get order details
        $order_details = get_order_details($order);

        // Generate barcode with the helper function
        $barcode_data_uri = get_order_barcode($order_id);
        $order_number_barcode_URL = esc_attr($barcode_data_uri);

        $shipping_address = esc_html($order_details['shipping_address']);
        $shipping_name = esc_html($order_details['shipping_name']);
        $shipping_address_line = esc_html($order_details['shipping_address_line']);
        $shipping_city = esc_html($order_details['shipping_city']);
        $shipping_state_zip = esc_html($order_details['shipping_state_zip']);
        $phone_number = esc_html($order_details['phone_number']);
        $order_date = esc_html($order_details['order_date']);
        $payment_method = esc_html($order_details['payment_method_title']);
        $customer_note = esc_html($order_details['customer_note']);

        // Get the total amount with comma separation
        $total_amount = number_format($order->get_total(), 0); // Formats the amount with zero decimal places and commas

        // Prepare the response
        $response = array(
            'success' => true,
            'order_id' => $order_id,
            'shipping_address' => $shipping_address,
            'shipping_name' => $shipping_name,
            'shipping_address_line' => $shipping_address_line,
            'shipping_city' => $shipping_city,
            'shipping_state_zip' => $shipping_state_zip,
            'phone_number' => $phone_number,
            /*'order_date' => $order_date,*/
            'payment_method' => $payment_method,
            'customer_note' => $customer_note,
            'total_amount' => $total_amount,
            'order_number_barcode_URL' => $order_number_barcode_URL,
        );

        // Send the response as JSON
        wp_send_json_success($response);
    } else {
        // If no order ID is found
        wp_send_json_error(array('message' => 'Order ID not found.'));
        wp_die();
    }
}
add_action('wp_ajax_generate_shipping', 'handle_generate_shipping');