/**
 * @file
 * Belgrade Theme JS.
 */
(function ($, Drupal, once) {

  'use strict';

  /**
   * Close behaviour.
   */
  Drupal.behaviors.quantityIncDec = {
    attach: function (context) {
      $(".number-btn").once().on("click", function () {
        let $button = $(this);
        let oldValue = parseInt($button.parent().find("input").val());
        let newVal;

        if ($button.text() === "+") {
          newVal = oldValue + 1;
        } else {
          // Don't allow decrementing below zero
          newVal = (oldValue > 0) ? oldValue - 1 : 0;
        }
        $button.parent().find("input").val(newVal);
      });

      $('.cart-block--offcanvas-cart-table__quantity input[type="number"]').once().on('change', function () {
        let priceItem = $(this).val() * parseFloat($(this).data('unitprice'));
        $(this).closest('.item-price').find('.cart-block--offcanvas-cart-table__price').html(priceItem.toFixed(2) + ' €');
      });

      // Field indication floating cart.
      $("#edit-field-indication, #edit-checkout-pane-reservation-field-indication-0-value").once().on('change keyup', function () {
        localStorage.setItem('indication', $(this).val());
      });
      let indication = localStorage.getItem('indication');
      if (indication) {
        $("#cart-field-indication").val(indication);
        $("#edit-field-indication").val(indication);
        $("#edit-checkout-pane-reservation-field-indication-0-value").val(indication);
      }

    }
  };

  $('.delete-order-item').on('click', function () {
    $('#'+$(this).attr('data-id')).click();
  });

  let screenWidth = window.innerWidth;
  if(screenWidth < 600) {
    jQuery('.cart-block--link__expand').removeClass('cart-block--link__expand');
  }

})(jQuery, Drupal, once);
