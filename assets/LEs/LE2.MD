### **JavaScript Code**

Update your `print-order.js` file to include this functionality:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const printInvoiceButton = document.getElementById('print-invoice');
    
    if (printInvoiceButton) {
        printInvoiceButton.addEventListener('click', () => {
            // Order ID is stored as a data attribute in the sidebar
            const orderId = printInvoiceButton.dataset.orderId;

            if (!orderId) {
                alert('Order ID is missing. Cannot generate invoice.');
                return;
            }

            // Fetch the order data from the server
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'generate_invoice',
                    order_id: orderId,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Open a new window and write the invoice HTML into it
                        const invoiceWindow = window.open('', '_blank', 'width=800,height=600');
                        if (invoiceWindow) {
                            invoiceWindow.document.write(data.html);
                            invoiceWindow.document.close(); // Close the document to apply styles
                            invoiceWindow.print(); // Trigger print dialog
                        } else {
                            alert('Failed to open the print window. Please check your browser settings.');
                        }
                    } else {
                        alert('Failed to fetch invoice data. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching invoice data:', error);
                    alert('An error occurred while generating the invoice.');
                });
        });
    }
});
```

---

### **Explanation**
1. **Fetch Order Data**
   - Sends an AJAX POST request to the WordPress server to fetch the invoice data for the specified order ID.

2. **Open a New Window**
   - If data is successfully fetched, it opens a new browser window and writes the invoice HTML into it.

3. **Trigger Print Dialog**
   - Automatically triggers the `print` dialog for the user.

---

### **HTML Update**
To pass the `order_id` to the JavaScript, modify the "Print Invoice" button in your meta box code in PHP:

```php
echo '<button id="print-invoice" class="button woocommerce-button" data-order-id="' . $order_id . '">Print Invoice</button>';
```

---

### Next Step
The above JavaScript expects the server to return the HTML for the invoice via an AJAX call. 

Would you like to implement the PHP part to fetch and format the order data?

---

To define `ajaxurl` globally in WordPress and ensure it is available for your JavaScript code, follow these steps:

---

### **1. Use `wp_localize_script` in PHP**

The `ajaxurl` variable is a standard way to refer to the WordPress AJAX endpoint (`admin-ajax.php`). You need to define it so that your JavaScript can use it without hardcoding the URL.

Update your `wc_print_buttons_enqueue_scripts` function in your plugin like this:

```php
function wc_print_buttons_enqueue_scripts( $hook ) {
    $current_screen = get_current_screen();

    if ( $current_screen->id === 'woocommerce_page_wc-orders' || $current_screen->post_type === 'shop_order' ) {
        $script_url = plugin_dir_url( __FILE__ ) . 'js/print-order.js';
        $style_url = plugin_dir_url( __FILE__ ) . 'css/print-order-style.css';

        // Enqueue scripts and styles
        wp_enqueue_script( 'wc-print-buttons-script', $script_url, array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'wc-print-buttons-style', $style_url );

        // Pass ajaxurl and other variables to JavaScript
        wp_localize_script( 'wc-print-buttons-script', 'ajax_object', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
    }
}
```

---

### **2. Use `ajax_object.ajaxurl` in JavaScript**

Once you define `ajaxurl` using `wp_localize_script`, it will be available in your JavaScript under the `ajax_object` variable.

Update your JavaScript code to reference it like this:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const printInvoiceButton = document.getElementById('print-invoice');

    if (printInvoiceButton) {
        printInvoiceButton.addEventListener('click', () => {
            const orderId = printInvoiceButton.dataset.orderId;

            if (!orderId) {
                alert('Order ID is missing. Cannot generate invoice.');
                return;
            }

            fetch(ajax_object.ajaxurl, { // Use ajax_object.ajaxurl instead of hardcoding
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'generate_invoice',
                    order_id: orderId,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        const invoiceWindow = window.open('', '_blank', 'width=800,height=600');
                        if (invoiceWindow) {
                            invoiceWindow.document.write(data.html);
                            invoiceWindow.document.close();
                            invoiceWindow.print();
                        } else {
                            alert('Failed to open the print window. Please check your browser settings.');
                        }
                    } else {
                        alert('Failed to fetch invoice data. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching invoice data:', error);
                    alert('An error occurred while generating the invoice.');
                });
        });
    }
});
```

---

### **How This Works**
1. **`wp_localize_script`:**
   - Passes PHP data (like the `ajaxurl`) into JavaScript as a global variable (`ajax_object`).

2. **JavaScript Usage:**
   - Instead of hardcoding the AJAX URL, you use `ajax_object.ajaxurl`, ensuring compatibility with different environments (e.g., live sites, development sites).

---
