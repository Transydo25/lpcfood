(function ($, Drupal) {

  'use strict';

  // This drives the interaction with the Geofield Leaflet Map.
  Drupal.behaviors.geofieldMapLeafletMapInteraction = {
    attach: function (context, settings) {
      // React on leaflet.map event.
      $(document).on('leaflet.map', function (e, settings, lMap, mapid) {
        let map = lMap;
        const markers = Drupal.Leaflet[mapid].markers;
        $('.row-store-list .marker-selector', context).each(function () {
          $(this).hover(
            function() {
              console.log(1);
              clearTimeout();
              $( this ).addClass("bg-light").css("text-decoration", "underline");
              const marker_id = $(this).data('marker-id');
              const center = markers[marker_id].getLatLng();
              map.setZoom(15);
              map.panTo(center);
              setTimeout(function () {
                if(markers[marker_id]) {
                  markers[marker_id].fire('click');
                }
              }, 300);
            }, function() {
              $( this ).removeClass("bg-light").css("text-decoration", "none");
              map.closePopup();
            }
          );
        });
      });
    }
  };

})(jQuery, Drupal);
