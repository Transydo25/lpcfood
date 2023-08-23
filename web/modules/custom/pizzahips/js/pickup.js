
(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.pickup = {
    attach: function (context, settings) {
      // function getToday() {
      //   let today = new Date();
      //   let dd = String(today.getDate()).padStart(2, '0');
      //   let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
      //   let yyyy = today.getFullYear();
      //   return yyyy + '-' + mm + '-' + dd;
      // }

      // // Hide the past hours of today
      // function hidePastTimeToday() {
      //   let today = new Date();
      //   let day = getToday();
      //   let statusDate = $('[name^="checkout_pane_reservation[field_pickup_date][0][value][date]"]').val();
      //   let dateCompare = new Date(statusDate);
      //   let dayNeedle = new Date(day);
      //   if(dateCompare.getTime() !== dayNeedle.getTime()){
      //     return false;
      //   }
      //   $(".time-pickup").each(function () {
      //     let dataTime = $(this).data('time');
      //     let dataHour = dataTime.split(':');
      //     if (today.getHours() >= parseInt(dataHour[0])) {
      //       $(this).parent().hide();
      //     }
      //   });
      // }

      function enableBtnValidateOrder(){
        $("#validateOrder").prop("disabled", false);
      }

      // // Selected input date Another Day.
      // function selectDateAnother(event) {
      //   let weekday = [
      //     Drupal.t("Sunday"), Drupal.t("Monday"), Drupal.t("Tuesday"), Drupal.t("Wednesday"),
      //     Drupal.t("Thursday"), Drupal.t("Friday"), Drupal.t(" Saturday")];
      //   let valueDateSelect = $('#dateAnother').val();
      //   let anotherDay = new Date(valueDateSelect);

      //   let dateSelect = valueDateSelect.split('-');
      //   let showDateSelect = weekday[anotherDay.getDay()] + ' ' + dateSelect[2] + '/' + dateSelect[1] + '/' + dateSelect[0];

      //   if (valueDateSelect !== '') {
      //     $('[name^="checkout_pane_reservation[field_pickup_date][0][value][date]"]').val(valueDateSelect);
      //     $('.pickup-date-text').html(showDateSelect);
      //     $('.activeSelectDate').removeClass('activeSelectDate');
      //     $('#dateAnother').closest('button').addClass('activeSelectDate');
      //     $(".time-pickup").parent().show();
      //   }
      // }

      // // Select the location, it should be transform to an entity reference field.
      function selectLocation() {
        let addressSelect = $('.cart-page-products-list #search').val();
        let idLocationSelect = $('#datalistOptions option').filter(function () {
          return this.value == addressSelect;
        }).data('value');
        let storeSelect = $('#datalistOptions option').filter(function () {
          return this.value == addressSelect;
        }).text();

        /* if value doesn't match an option, idLocationSelect will be undefined*/
        if (idLocationSelect) {
          let addressLocationSelect = $('#datalistOptions option').filter(function () {
            return this.value == addressSelect;
          }).data('address');

          let nameLocation = addressSelect + '<br/>' + addressLocationSelect;
          $('#showAddressSelect').html(nameLocation);
          idLocationSelect = addressSelect + ' (' + idLocationSelect + ')';
          $('[name^="checkout_pane_reservation[field_store_selected]"]').val(idLocationSelect);
          // Archive store id for next times.
          let saveLocation = drupalSettings.pizzahips.save_history_search_map;
          $.post(saveLocation, {idLocation: idLocationSelect, nameLocation: nameLocation});
        }
      }
      // // $('.time-pickup').once().on('click',function (){
      // //   enableBtnValidateOrder();
      // // });

      // $('#dateAnother').once().on('change',function (event){
      //   selectDateAnother(event);
      // });

      $('#search').once().on('change',function (){
        selectLocation();
      });

      // hidePastTimeToday();

      // $(window).once().keydown(function (event) {
      //   if (event.keyCode == 13) {
      //     event.preventDefault();
      //     return false;
      //   }
      // });

      // // Set first date is default.
      // $('.date-pickup:first').once().addClass('activeSelectDate');

      // // Set time default.
      // $('#edit-checkout-pane-reservation-field-pickup-date-wrapper').once().ready(function (){
      //   let time = $('[name^="checkout_pane_reservation[field_pickup_date][0][value][time]"]').val();
      //   $('.activeSelectTime').removeClass('activeSelectTime');
      //   $(".time-pickup[data-time='"+time+"']").addClass('activeSelectTime');
      // });

      // $(".date-pickup").on('click', function () {
      //   let valueDateSelect = $(this).data('date');
      //   let today = getToday();
      //   // add class activeSelectDate to date selected
      //   $('.activeSelectDate').removeClass('activeSelectDate');
      //   $(this).addClass('activeSelectDate');

      //   if (valueDateSelect != '') {

      //     $('[name^="checkout_pane_reservation[field_pickup_date][0][value][date]"]').val(valueDateSelect);
      //     $('.pickup-date-text').html($(this).text());

      //     // if today we must disable all past hour.
      //     if (valueDateSelect == today) {
      //       hidePastTimeToday();
      //     } else {
      //       $(".time-pickup").each(function () {
      //         $(this).parent().show();
      //       });
      //     }
      //   }
      // });

      // $(".time-pickup").on('click', function () {
      //   $('.pickup-time-text').html($(this).text());
      //   $('[name^="checkout_pane_reservation[field_pickup_date][0][value][time]"]').val($(this).data('time'));
      //   $('.activeSelectTime').removeClass('activeSelectTime');
      //   $(this).addClass('activeSelectTime');
      // });

      if($('#search').val() != ""){
        enableBtnValidateOrder();
      }

    }
  }
}(jQuery, Drupal, drupalSettings, once));

