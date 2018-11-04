(function ($, Drupal) {

  'use strict';

  /**
   * Enable Tilt.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map tilt functionality to relevant elements.
   */
  Drupal.behaviors.geolocationTilt = {
    attach: function (context, drupalSettings) {

      Drupal.geolocation.executeFeatureOnAllMaps(
        'map_disable_tilt',
        function (map, featureSettings) {
          map.addInitializedCallback(function (map) {
            map.googleMap.setTilt(0);
          });

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };
})(jQuery, Drupal);
