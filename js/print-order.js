jQuery(document).ready(function($) {
    /*
    console.log("jQuery:", $); // Log jQuery for debugging
    console.log('ajaxurl:', ajax_object.ajaxurl); // Log ajaxurl for debugging
    */

    // Handle the Print Invoice button click
    $('#print-invoice').click(function(e) {
        e.preventDefault(); // Prevent default button behavior
        console.log('Print Invoice clicked');
        let orderId = $(this).data('order-id'); // Get order ID from data attribute [LE2]

        $.ajax({
            url: ajax_object.ajaxurl,
            method: 'POST',
            data: {
                action: 'generate_invoice',
                order_id: orderId
            },
            success: function (response) {
                console.log("print-invoice success:", response); // Check the full response in the console
                if (response.success && response.data.invoice) {
                    let cssUrl = `${ajax_object.plugin_url}/css/print-order-style.css`;
                    /*console.log("CSS File URL:", cssUrl);*/

                    let logoUrl = `${ajax_object.plugin_url}/images/logo.png`;
                    let compAddress = ajax_object.comp_address;
                    let thankyouMessage = ajax_object.thankyou_message;
                    let compPhoneNumber = ajax_object.comp_phone_number;
                    let compEmailAddress = ajax_object.comp_email_address;
                    let invoiceContent = `
                    <html>
                        <head>
                            <title>Invoice</title>
                            <link rel="stylesheet" type="text/css" href="${cssUrl}" media="all">
                        </head>
                        <body>
                            <div id="invoice-container">
                                <div id="invoice-header">
                                    <div class="header-content">
                                        <div class="logo-section">
                                            <img src="${logoUrl}" alt="Myanmar Lifestyle" />
                                            <div class="order-info">
                                                <p><span>Order No:&nbsp;</span> ${response.data.order_id}</p>
                                                <p><img src="${response.data.order_number_barcode_URL}"></p>
                                                <p><span>Date:&nbsp;</span> ${response.data.order_date}</p>
                                                <p><span>Phone Number:&nbsp;</span> ${response.data.phone_number}</p>
                                            </div>
                                        </div>
                                        <div class="address-section">
                                            <div class="from-and-to-addresses">
                                                <div class="from-address">
                                                    <div class="address-header"><span>From:</span></div>
                                                    <div class="address-details">${compAddress}</div>
                                                </div>
                                                <div class="to-address">
                                                    <div class="shipping-address-header"><span>Shipping Address:</div>
                                                    <div class="address-details">${response.data.shipping_address}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                ${response.data.invoice}

                                <div id="footer-container">
                                    <div id="footer-message">${thankyouMessage}</div>
                                    <div id="footer-contact-info">
                                        <div>Customer Support:&nbsp;</div>
                                        <div>${compPhoneNumber}</div>
                                        <div>&nbsp;|&nbsp;</div>
                                        <div>${compEmailAddress}</div>
                                    </div>
                                </div>
                            </div>
                        </body>
                    </html>`;

                    let printWindow = window.open('', '_blank'); // Open a new print window
                    printWindow.document.write(invoiceContent); // Write the HTML content
                    printWindow.document.close(); // Close the document to signal the browser to load it

                    // Wait for the print window to fully load the CSS and content
                    printWindow.onload = function () {
                        console.log("Print window content loaded and styled.");
                        console.log("Stylesheets:", printWindow.document.styleSheets);
                        printWindow.print(); // Trigger the print dialog
                    };
                } else {
                    alert('Failed to fetch invoice data. Please try again.');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Handle the Print Shipping button click
    $('#print-shipping').click(function(e) {
        e.preventDefault(); // Prevent default button behavior
        console.log('Print Shipping clicked');
        let orderId = $(this).data('order-id'); // Get order ID

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_shipping', // Your custom AJAX action
                order_id: orderId,
            },
            success: function(response) {
                console.log("print-shipping success:", response); // Check the full response in the console
                if (response.success) {
                    
                    let cssUrl = `${ajax_object.plugin_url}/css/print-order-style.css`;
                    /*console.log("CSS File URL:", cssUrl);*/

                    /*
                    console.log(`shipping name:${response.data.shipping_name}`);
                    console.log(`shipping address line:${response.data.shipping_address_line}`);
                    console.log(`shipping city:${response.data.shipping_city}`);
                    console.log(`shipping state:${response.data.shipping_state_zip}`);
                    */

                    let logoUrl = `${ajax_object.plugin_url}/images/logo.png`;
                    let compName = ajax_object.comp_name;
                    let compAddress = ajax_object.comp_address;
                    let compPhoneNumber = ajax_object.comp_phone_number;
                    let shippingContent = `
                    <html>
                        <head>
                            <title>Shipping</title>
                            <link rel="stylesheet" type="text/css" href="${cssUrl}" media="all">
                        </head>
                        <body>
                            <!--div id="shipping-header">${compName}</div-->
                            <div id="shipping-body">
                                <div id="order-detail">
                                    <div class="comp-logo"><img src="${logoUrl}" alt="Myanmar Lifestyle" /></div>
                                    <div class="order-number-barcode"><img src="${response.data.order_number_barcode_URL}"></div>
                                    <div class="order-number">Order No:&nbsp${response.data.order_id}</div>
                                    <table class="order-info-table">
                                        <tr>
                                            <td>Total Amount:</td>
                                            <td><span>${response.data.total_amount}</span></td>
                                        </tr>
                                        <tr>
                                            <td>Payment:</td>
                                            <td>${response.data.payment_method}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id="shipping-address">
                                    <div class="shipping-name">${response.data.shipping_name}</div>
                                    <div class="shipping-phone"><span>Tel:&nbsp;</span>${response.data.phone_number}</div>
                                    <div class="shipping-address">${response.data.shipping_address_line}</div>
                                    <div id="shipping-state-city-combined">
                                        <div class="shipping-city">${response.data.shipping_city}</div>
                                        <div class="shipping-state">${response.data.shipping_state_zip}</div>
                                    </div>
                                    <div class="shipping-note"><span>မှတ်ချက် ။&nbsp;</span>${response.data.customer_note}</div>
                                </div>
                            </div>
                            <div id="shipping-footer">
                                <div class="shipping-footer-address">${compAddress}</div>
                                <div class="shipping-footer-tel">Tel:&nbsp;${compPhoneNumber}</div>
                            </div>
                        </body>
                    </html>
                    `;

                    // Open a new window and display the invoice content                    
                    let printWindow = window.open('', '_blank');
                    printWindow.document.write(shippingContent); // Write the HTML content
                    printWindow.document.close(); // Close the document to signal the browser to load it

                    // Wait for the print window to fully load the CSS and content
                    printWindow.onload = function () {
                        /*
                        console.log("Print window content loaded and styled.");
                        console.log("Stylesheets:", printWindow.document.styleSheets);
                        */
                        printWindow.print(); // Trigger the print dialog
                    };
                } else {
                    console.log('Shipping generation failed: ', response); // Log error response
                    alert('Failed to generate shipping label.');
                }
            },
            error: function(error) {
                console.log('AJAX Request Error:', error);
            }
        });
    });
});