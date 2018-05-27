(function ($, Drupal) {

  'use strict';

  /**
   * Geocoder control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationControlGeocoder = {
    attach: function (context, drupalSettings) {

      Drupal.geolocation.executeFeatureOnAllMaps(
        'control_geocoder',
        function (map, featureSettings) {
          Drupal.geolocation.geocoder.addResultCallback(function(address) {
            map.setCenterByCoordinates({lat: address.geometry.location.lat(), lng: address.geometry.location.lng()}, undefined, 'google_control_geocoder');
          }, map.id);

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };

})(jQuery, Drupal);
