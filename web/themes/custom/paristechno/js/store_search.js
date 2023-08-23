(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.store_list = {
    attach: function (context, settings) {
      const searchInput = $('#search-input', context);
      const storeRows = $('.marker-selector', context);

      function handleKeyUp() {
        const search = $(this).val().toUpperCase();
        storeRows.hide().each(function () {
          const name = $(this).find('.store-name').text().toUpperCase();
          const address = $(this).find('.store-address').text().toUpperCase();
          if (address.indexOf(search) !== -1 || name.indexOf(search) !== -1) {
            $(this).show();
          }
        });
      }

      searchInput.keyup(handleKeyUp);
    }
  }
})(jQuery, Drupal, drupalSettings, once);
