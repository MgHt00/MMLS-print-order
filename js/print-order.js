jQuery(document).ready(function($) {
  console.log('ajaxurl:', ajax_object.ajaxurl); // Log ajaxurl for debugging

  // Handle the Print Invoice button click
  $('#print-invoice').click(function(e) {
      e.preventDefault(); // Prevent default button behavior
      console.log('Print Invoice clicked');
      var orderId = $(this).data('order-id'); // Get order ID from data attribute [LE2]

      $.ajax({
          url: ajax_object.ajaxurl,
          type: 'POST',
          data: {
              action: 'generate_invoice', // Your custom AJAX action
              order_id: orderId,
          },
          success: function(response) {
              if (response.success) {
                  // Open a new window and display the invoice content
                  var printWindow = window.open('', '_blank');
                  printWindow.document.write(response.data); // Write invoice HTML to the new window
                  printWindow.document.close();
                  printWindow.print(); // Trigger print dialog
              } else {
                  alert('Failed to generate invoice.');
              }
          },
          error: function(error) {
              console.log('AJAX Request Error:', error);
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
                  // Open a new window and display the shipping content
                  var printWindow = window.open('', '_blank');
                  printWindow.document.write(response.data); // Write shipping HTML to the new window
                  printWindow.document.close();
                  printWindow.print(); // Trigger print dialog
              } else {
                  alert('Failed to generate shipping label.');
              }
          },
          error: function(error) {
              console.log('AJAX Request Error:', error);
          }
      });
  });
});
