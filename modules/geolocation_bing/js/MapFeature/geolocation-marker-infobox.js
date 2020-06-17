/**
 * @file
 * Marker Infobox for Bing map.
 */

(function (Drupal) {

  'use strict';

  /**
   * Marker Infobox.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMarkerInfobox = {
    attach: function (context, drupalSettings) {

      Drupal.geolocation.executeFeatureOnAllMaps(
        'bing_marker_infobox',

        function (map, featureSettings) {

          map.addMarkerAddedCallback(function (currentMarker) {


          });

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {
    }
  };
})(Drupal);
