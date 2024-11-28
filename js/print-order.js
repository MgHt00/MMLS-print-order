jQuery(document).ready(function($) {
  console.log("jQuery:", $); // This should work
  console.log('ajaxurl:', ajax_object.ajaxurl); // Log ajaxurl for debugging

// Handle the Print Invoice button click
$('#print-invoice').click(function(e) {
    e.preventDefault(); // Prevent default button behavior
    console.log('Print Invoice clicked');
    var orderId = $(this).data('order-id'); // Get order ID from data attribute [LE2]

    $.ajax({
        url: ajax_object.ajaxurl,
        method: 'POST',
        data: {
            action: 'generate_invoice',
            order_id: orderId
        },
        success: function (response) {
          console.log(response); // Check the full response in the console
          if (response.success && response.data.invoice) {
            // Successfully fetched the invoice content
            var cssUrl = `${ajax_object.plugin_url}/css/print-order-style.css`;
            console.log("CSS File URL:", cssUrl);

            var invoiceContent = `
            <html>
                <head>
                    <title>Invoice</title>
                    <link rel="stylesheet" type="text/css" href="${cssUrl}" media="all">
                </head>
                <body>
                    <div id="invoice-container">
                        ${response.data.invoice}
                    </div>
                </body>
            </html>`;


            var printWindow = window.open('', '_blank'); // Open a new print window
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
    var orderId = $(this).data('order-id'); // Get order ID

    $.ajax({
        url: ajax_object.ajaxurl,
        type: 'POST',
        data: {
            action: 'generate_shipping', // Your custom AJAX action
            order_id: orderId,
        },
        success: function(response) {
            if (response.success) {
              // Open a new window and display the invoice content                    
              var printWindow = window.open('', '_blank');
              printWindow.document.write('<html><head><title>Invoice</title></head><body>'); // Open a full HTML document
              printWindow.document.write(response.data); // Write invoice HTML to the new window
              printWindow.document.write('</body></html>'); // Close the HTML document
              printWindow.document.close();
              printWindow.print(); // Trigger print dialog
            } else {
              console.log('Invoice generation failed: ', response); // Log error response
              alert('Failed to generate shipping label.');
            }
        },
        error: function(error) {
            console.log('AJAX Request Error:', error);
        }
    });
});
});