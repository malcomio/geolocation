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
      Drupal.geolocation.executeFeatureOnAllMaps(
        'leaflet_marker_clusterer',

        /**
         * @param {GeolocationLeafletMap} map - Current map.
         * @param {GeolocationMapFeatureSettings} featureSettings - Settings for current feature.
         */
        function (map, featureSettings) {
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

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };
})(jQuery, Drupal);
