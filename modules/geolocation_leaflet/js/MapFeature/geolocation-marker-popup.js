/**
 * @typedef {Object} LeafletMarkerPopupSettings
 *
 * @property {String} enable
 * @property {bool} infoAutoDisplay
 */

/**
 * @typedef {Object} LeafletPopup
 * @property {Function} open
 * @property {Function} close
 */

/**
 * @property {LeafletPopup} GeolocationGoogleMap.infoWindow
 * @property {function({}):LeafletPopup} GeolocationGoogleMap.InfoWindow
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
  Drupal.behaviors.geolocationLeafletMarkerPopup = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {LeafletMarkerPopupSettings} mapSettings.marker_popup - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.marker_popup !== 'undefined'
            && mapSettings.marker_popup.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addMarkerAddedCallback(function (currentMarker) {
              if (typeof (currentMarker.locationWrapper) === 'undefined') {
                return;
              }

              var content = currentMarker.locationWrapper.find('.location-content');

              if (content.length < 1) {
                return;
              }
              currentMarker.bindPopup(content.html());

              if (mapSettings.marker_popup.infoAutoDisplay) {
                currentMarker.openPopup();
              }
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
