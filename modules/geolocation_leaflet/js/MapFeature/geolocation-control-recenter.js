/**
 * @typedef {Object} ControlRecenterSettings
 *
 * @extends {GeolocationMapFeatureSettings}
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Recenter control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map recenter functionality to relevant elements.
   */
  Drupal.behaviors.leafletControlRecenter = {
    attach: function (context, drupalSettings) {
      Drupal.geolocation.executeFeatureOnAllMaps(
        'leaflet_control_recenter',
        function (map, featureSettings) {
          map.addInitializedCallback(function (map) {
            var recenterButton = $('.geolocation-map-control .recenter', map.wrapper);
            recenterButton.click(function (e) {
              map.setCenter();
              e.preventDefault();
            });
          });

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };

})(jQuery, Drupal);
