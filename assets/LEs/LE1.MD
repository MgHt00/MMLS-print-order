The issue seems to arise because the current logic only differentiates between editing and adding new orders based on whether an order ID exists. However, on the "Add New Order" page (`post-new.php`), the screen ID will still be `shop_order`, and `$_GET['id']` or `$post->ID` will not be present. We need to explicitly check if we are on the "Add New Order" page.

### Solution:
We can add a condition to check if the current URL contains `post-new.php` to identify the "Add New Order" page and prevent the buttons from being displayed there.

### Updated Code:
```php
// Render the content of the meta box
function wc_print_buttons_meta_box_content() {
    global $post;

    // Check if we are on the "Add New Order" page [LE1]
    $current_url = $_SERVER['REQUEST_URI'];
    $is_new_order_page = strpos($current_url, 'post-new.php?post_type=shop_order') !== false;

    if ($is_new_order_page) {
        // Do not display anything for the "Add New Order" page
        echo '<p>This section is not available for new orders.</p>';
        return;
    }

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
        // Do not display anything for invalid pages
        echo '<p>This section is not available for new orders.</p>';
    }
}
```

### Key Changes:
1. **Check for "Add New Order" Page**:
   - Used `$_SERVER['REQUEST_URI']` to check if the current URL contains `post-new.php?post_type=shop_order`. This ensures the code explicitly identifies the "Add New Order" page.

2. **Return Early for New Orders**:
   - If on the "Add New Order" page, it displays a message and exits early to prevent buttons from rendering.

### Why This Works:
- The `post-new.php` check ensures the meta box only displays on valid order edit pages and avoids any display issues on the "Add New Order" page.
- The logic is robust for both local and live environments since URLs are used consistently.

Let me know if this resolves the issue! 😊