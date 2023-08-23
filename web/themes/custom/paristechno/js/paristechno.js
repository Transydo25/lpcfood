(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.product = {
    attach: function (context, settings) {
      $(once('product_js', '#block-paristechno-content', context)).each(function () {
          if ($("#edit-field-sale").val() !== "0") {
            $(".field--name-field-price").hide();
            $(".field--name-field-sale-percent").hide()
          }
          $("#edit-field-sale").change(function(){
            console.log($(this).not(':selected').val());
            if ($(this).not(':selected').val() !== "0") {
              $(".field--name-field-price").hide();
              $(".field--name-field-sale-percent").hide()
             }
             else {
              $(".field--name-field-price").show();
              $(".field--name-field-sale-percent").show()
             }
          })
          $('input[name^="field_sale_percent"]').change(function(){
            let valuePrecent = $(this).val();
            let valCost = $('input[name^="field_cost"]').val();
            let value = Number(valCost)-(Number(valuePrecent)/100 * Number(valCost));
            $('input[name^="field_price"]').val(value);
          })
      })
    }
  }
})(jQuery, Drupal, drupalSettings, once);
