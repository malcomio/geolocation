/**
 * @typedef {Object} LeafletMarkerClustererSettings
 *
 * @property {String} enable
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Marker InfoWindow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationLeafletMarkerClusterer = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {LeafletMarkerClustererSettings} mapSettings.leaflet_marker_clusterer - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.leaflet_marker_clusterer !== 'undefined'
            && mapSettings.leaflet_marker_clusterer.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            if (map.container.hasClass('leaflet-marker-cluster-processed')) {
              return;
            }
            map.container.addClass('leaflet-marker-cluster-processed');

            var cluster = L.markerClusterGroup();
            map.leafletMap.removeLayer(map.markerLayer);
            cluster.addLayer(map.markerLayer);

            map.leafletMap.addLayer(cluster);

            map.addMarkerAddedCallback(function (currentMarker) {
              cluster.addLayer(currentMarker);
            });

            map.addMarkerRemoveCallback(function (marker) {
              cluster.removeLayer(marker);
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
