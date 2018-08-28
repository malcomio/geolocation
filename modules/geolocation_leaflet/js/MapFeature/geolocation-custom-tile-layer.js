/**
 * @typedef {Object} CustomTileLayerSettings
 *
 * @extends {GeolocationMapFeatureSettings}
 *
 * @property {String} tileLayerUrl
 * @property {String} tileLayerAttribution
 * @property {String} tileLayerSubdomains
 */

(function ($, Drupal) {

  'use strict';

  /**
   * CustomTileLayerSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches CustomTileLayerSettings functionality to relevant elements.
   */
  Drupal.behaviors.leafletCustomTileLayer = {
    attach: function (context, drupalSettings) {
      Drupal.geolocation.executeFeatureOnAllMaps(
        'leaflet_custom_tile_layer',

        /**
         * @param {GeolocationLeafletMap} map - Current map.
         * @param {CustomTileLayerSettings} featureSettings - Settings for current feature.
         */
        function (map, featureSettings) {
          map.tileLayer.remove();
          L.tileLayer(featureSettings.tileLayerUrl, {
            attribution: featureSettings.tileLayerAttribution,
            subdomains: featureSettings.tileLayerSubdomains
          }).addTo(map.leafletMap);

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };
})(jQuery, Drupal);
