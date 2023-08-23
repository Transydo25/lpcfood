(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.store_list = {
    attach: function (context, settings) {
      // Save history when user click on store.
      $('.get-store,.show-info').once().click(function () {
        // scroll to top if mobile.
        if (/Mobi|Android/i.test(navigator.userAgent)) {
          $("body").animate({
            scrollTop: $('.navbar').height()
          }, 1000);
        }
        let idLocation = $(this).data('store-id');
        let nameLocation = $(this).find('address').html();
        let homeUrl = drupalSettings.pizzahips.save_history_search_map;
        $.post(homeUrl, {idLocation: idLocation, nameLocation: nameLocation});
      });

      // Calculate height of map
      let heightBlockSearchMap = window.innerHeight - $('.navbar').height();
      $('#block-map-search-block').once().height(heightBlockSearchMap - 40);
      $('#leaflet-map-view-notre-carte-store-map').height(heightBlockSearchMap);

      $('.icon_get_location').once().click(function () {
        getLocation();
      });

      moveStoreListToLeft();
      searchAddress();

      function getLocation() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(showPosition);
        } else {
          alert(Drupal.t('Geolocation is not supported by this browser.'));
        }
      }

      function showPosition(position) {
        window.location = homeUrl + 'nos-corners/' + position.coords.latitude + ',' + position.coords.longitude + '<=20km';
      }

      function moveStoreListToLeft() {
        $('.status').append(function () {
          return $(this).closest('.container').find('.office-hours-status');
        });
        if ($('.map-search').length) {
          $('.store-list .row-store-list').appendTo('.map-search');
        }
        if ($('.submit-search').length) {
          $('.submit-search').insertAfter('#block-map-search-block input#edit-search');
          $('.submit-search').on('click', function () {
            $(this).closest('form').submit();
          });
        }
      }

      function searchAddress() {
        $('#search-store-form input#edit-search').keyup(function () {
          let search = $(this).val();
          $('.row-store-list').hide();
          $('.row-store-list').each(function () {
            if ($(this).find('address').text().toUpperCase().indexOf(search.toUpperCase()) != -1) {
              $(this).show();
            }
          });
        });

      }

      $( ".row-store-list" ).wrapAll( "<div class='d-flex d-md-block' />");
    }
  }
}(jQuery, Drupal, drupalSettings, once));
