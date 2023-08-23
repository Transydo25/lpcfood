(function ($, Drupal) {

  'use strict';
  
  Drupal.behaviors.geofieldMapLeafletMapInteraction = {
    attach: function (context, settings) {  // React on leaflet.map event.
      $(document).on('leaflet.map', function (e, settings, lMap, mapid) {
        const markers = Drupal.Leaflet[mapid].markers;
        const activeClassName = "bg-light shadow-sm text-decoration-underline";

        function handleMarkerClick() {
          $('.marker-selector', context).removeClass(activeClassName);
          $(this).addClass(activeClassName);

          const markerId = $(this).data('marker-id');
          const center = markers[markerId].getLatLng();
          lMap.setZoom(15);
          lMap.panTo(center);

          setTimeout(function () {
            if (markers[markerId]) {
              markers[markerId].fire('click');
            }
          }, 300);
        }

        $('.marker-selector', context).on('click', handleMarkerClick);
      });
    }
  };

})(jQuery, Drupal);
