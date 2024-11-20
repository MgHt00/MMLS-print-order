jQuery(document).ready(function ($) {
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
});