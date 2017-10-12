/**
 * @typedef {Object} ControlGeocoderSettings
 *
 * @property {String} enable
 */

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
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - canvasId of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlGeocoderSettings} mapSettings.control_geocoder - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_geocoder !== 'undefined'
            && mapSettings.control_geocoder.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            map.addReadyCallback(function (map) {
              var geocoderInput = $('<input data-source-identifier="' + mapId + '" class="geocoder" />');

              map.addControl(geocoderInput);
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
