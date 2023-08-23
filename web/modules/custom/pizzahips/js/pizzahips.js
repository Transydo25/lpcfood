(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.pizzahips = {
    attach: function (context, settings) {
      var baseUrl = drupalSettings.path.baseUrl;
      var langCode = drupalSettings.path.currentLanguage;
      var homeUrl = baseUrl + langCode + '/';

      $('.icon_get_location').click(function () {
        getLocation();
      });

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
    }
  }
}(jQuery, Drupal, drupalSettings, once));
