/**
 * @typedef {Object} MarkerClustererSettings
 *
 * @property {String} enable
 * @property {String} imagePath
 * @property {Object} styles
 * @property {string} maxZoom
 */

(function ($, Drupal) {

  'use strict';

  /**
   * MarkerClustererSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMarkerClusterer = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - canvasId of current map
         * @param {Object} mapSettings - settings for current map
         * @param {MarkerClustererSettings} mapSettings.marker_clusterer - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.marker_clusterer !== 'undefined'
            && mapSettings.marker_clusterer.enable
            && typeof MarkerClusterer !== 'undefined'
          ) {

            /* global MarkerClusterer */

            var imagePath = '';
            if (mapSettings.marker_clusterer.imagePath) {
              imagePath = mapSettings.marker_clusterer.imagePath;
            }
            else {
              imagePath = 'https://cdn.rawgit.com/googlemaps/js-marker-clusterer/gh-pages/images/m';
            }

            var markerClustererStyles = '';
            if (typeof mapSettings.marker_clusterer.styles !== 'undefined') {
              markerClustererStyles = mapSettings.marker_clusterer.styles;
            }

            var map = Drupal.geolocation.getMapById(mapId);

            map.addLoadedCallback(function (map) {
              new MarkerClusterer(
                map.googleMap,
                map.mapMarkers,
                {
                  imagePath: imagePath,
                  styles: markerClustererStyles,
                  maxZoom: parseInt(mapSettings.marker_clusterer.maxZoom)
                }
              );
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
