jQuery(document).ready(function($) {
  console.log('ajaxurl:', ajax_object.ajaxurl); // Log ajaxurl

  // Example: Make a basic AJAX request to check the URL
  $.ajax({
      url: ajax_object.ajaxurl,
      type: 'POST',
      data: {
          action: 'test_ajax_action', // A test action
      },
      success: function(response) {
          console.log('AJAX Request Success:', response);
      },
      error: function(error) {
          console.log('AJAX Request Error:', error);
      }
  });
});


document.addEventListener('DOMContentLoaded', () => {
  const printInvoiceButton = document.getElementById('print-invoice');

  if (printInvoiceButton) {
      printInvoiceButton.addEventListener('click', () => {
          const orderId = printInvoiceButton.dataset.orderId;

          if (!orderId) {
              alert('Order ID is missing. Cannot generate invoice.');
              return;
          }

          fetch(ajax_object.ajaxurl, { // Use ajax_object.ajaxurl instead of hardcoding [LE2]
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


/*jQuery(document).ready(function ($) {
  $('#print-invoice').on('click', function () {
      let win = window.open('', '_blank');
      win.document.write('<h1>Invoice</h1><p>This is a dummy invoice.</p>');
      win.document.close();
      win.print();
  });

  $('#print-shipping').on('click', function () {
      let win = window.open('', '_blank');
      win.document.write('<h1>Shipping Info</h1><p>This is dummy shipping info.</p>');
      win.document.close();
      win.print();
  });
});*/