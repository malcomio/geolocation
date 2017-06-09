/**
 * @file
 * Handle the common map.
 */

/**
 * @typedef {Object} GooglePolyline
 * @property {function(GoogleMap)} setMap
 */

/**
 * @typedef {Object} GooglePolygon
 * @property {function(GoogleMap)} setMap
 */

/**
 * @typedef {Object} GoogleMap
 * @property {function(Object):GooglePolyline} Polyline
 * @property {function(Object):GooglePolygon} Polygon
 */

/**
 * @typedef {Object} CommonMapDrawSettings
 * @property {boolean} polyline
 * @property {string} strokeColor
 * @property {string} strokeOpacity
 * @property {string} strokeWeight
 * @property {boolean} geodesic
 * @property {boolean} polygon
 * @property {string} fillColor
 * @property {string} fillOpacity
 */

/**
 * @typedef {Object} GeolocationMapSettings
 * @property {CommonMapDrawSettings} settings.common_google_map_drawing_settings
 */

/**
 * @property {CommonMapSettings[]} drupalSettings.geolocation.commonMap
 */

/**
 * @property {function(CommonMapUpdateSettings)} GeolocationMapSettings.updateDrupalView
 */

(function ($, Drupal) {

  'use strict';

  /**
   * @namespace
   */
  Drupal.geolocation = Drupal.geolocation || {};

  /**
   * Draw with multiple locations on map.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Draw with multiple locations on map.
   */
  Drupal.behaviors.geolocationCommonMapDraw = {
    attach: function (context, drupalSettings) {
      if (typeof Drupal.geolocation.loadGoogle === 'function') {
        // First load the library from google.
        Drupal.geolocation.loadGoogle(function () {
          initialize2(drupalSettings.geolocation.maps, context);
        });
      }
    }
  };

  /**
   * Runs after the Google Maps API is available
   *
   * @param {GeolocationMapSettings[]} mapSettings - The geolocation map objects.
   * @param {object} context - The html context.
   */
  function initialize2(mapSettings, context) {

    $.each(
      mapSettings,

      /**
       * @param {string} mapId - Current map ID
       * @param {GeolocationMapSettings} map - Single map settings Object
       */
      function (mapId, map) {
        // Get the map container.
        /** @type {jQuery} */
        var mapWrapper = $('#' + mapId, context).first();

        if (!mapWrapper.length) {
          return;
        }

        if (!map.settings.hasOwnProperty('common_google_map_drawing_settings')) {
          return;
        }

        if (
          !map.settings.common_google_map_drawing_settings.polyline
          && !map.settings.common_google_map_drawing_settings.polygon
        ) {
          return;
        }

        Drupal.geolocation.addMapLoadedCallback(function (map) {

          var locations = [];

          $('#' + map.id, context).find('.geolocation-common-map-locations .geolocation').each(function (index, location) {
            /** @var jQuery */
            location = $(location);
            locations.push(new google.maps.LatLng(Number(location.data('lat')), Number(location.data('lng'))));
          });

          if (!locations.length) {
            return;
          }

          if (map.settings.common_google_map_drawing_settings.polygon) {
            var polygon = new google.maps.Polygon({
              paths: locations,
              strokeColor: map.settings.common_google_map_drawing_settings.strokeColor,
              strokeOpacity: map.settings.common_google_map_drawing_settings.strokeOpacity,
              strokeWeight: map.settings.common_google_map_drawing_settings.strokeWeight,
              geodesic: map.settings.common_google_map_drawing_settings.geodesic,
              fillColor: map.settings.common_google_map_drawing_settings.fillColor,
              fillOpacity: map.settings.common_google_map_drawing_settings.fillOpacity
            });
            polygon.setMap(map.googleMap);
          }

          if (map.settings.common_google_map_drawing_settings.polyline) {
            var polyline = new google.maps.Polyline({
              path: locations,
              strokeColor: map.settings.common_google_map_drawing_settings.strokeColor,
              strokeOpacity: map.settings.common_google_map_drawing_settings.strokeOpacity,
              strokeWeight: map.settings.common_google_map_drawing_settings.strokeWeight,
              geodesic: map.settings.common_google_map_drawing_settings.geodesic
            });
            polyline.setMap(map.googleMap);
          }

        }, mapId);
      }
    );
  }
})(jQuery, Drupal);