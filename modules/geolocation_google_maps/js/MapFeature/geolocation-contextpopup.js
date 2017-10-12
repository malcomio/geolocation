/**
 * @typedef {Object} ContextPopupSettings
 *
 * @property {String} enable
 * @property {String} content
 */

(function ($, Drupal) {

  'use strict';

  /**
   * ContextPopupSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationContextPopup = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - canvasId of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ContextPopupSettings} mapSettings.context_popup - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.context_popup !== 'undefined'
            && mapSettings.context_popup.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            map.addReadyCallback(function (map) {

              /** @param {jQuery} */
              var contextContainer = jQuery('<div class="geolocation-context-popup"></div>');
              contextContainer.hide();
              contextContainer.appendTo(map.container);

              /**
               * Context popup handling.
               *
               * @param {GoogleMapLatLng} latLng - Coordinates.
               * @return {GoogleMapPoint} - Pixel offset against top left corner of map container.
               */
              map.googleMap.fromLatLngToPixel = function (latLng) {
                var numTiles = 1 << map.googleMap.getZoom();
                var projection = map.googleMap.getProjection();
                var worldCoordinate = projection.fromLatLngToPoint(latLng);
                var pixelCoordinate = new google.maps.Point(
                  worldCoordinate.x * numTiles,
                  worldCoordinate.y * numTiles);

                var topLeft = new google.maps.LatLng(
                  map.googleMap.getBounds().getNorthEast().lat(),
                  map.googleMap.getBounds().getSouthWest().lng()
                );

                var topLeftWorldCoordinate = projection.fromLatLngToPoint(topLeft);
                var topLeftPixelCoordinate = new google.maps.Point(
                  topLeftWorldCoordinate.x * numTiles,
                  topLeftWorldCoordinate.y * numTiles);

                return new google.maps.Point(
                  pixelCoordinate.x - topLeftPixelCoordinate.x,
                  pixelCoordinate.y - topLeftPixelCoordinate.y
                );
              };

              google.maps.event.addListener(map.googleMap, 'rightclick', function (event) {
                console.log(mapSettings.context_popup.content, "Contents");
                var content = Drupal.formatString(mapSettings.context_popup.content, {
                  '@lat': event.latLng.lat(),
                  '@lng': event.latLng.lng()
                });

                contextContainer.html(content);

                if (content.length > 0) {
                  var pos = map.googleMap.fromLatLngToPixel(event.latLng);
                  contextContainer.show();
                  contextContainer.css('left', pos.x);
                  contextContainer.css('top', pos.y);
                }
              });

              google.maps.event.addListener(map.googleMap, 'click', function (event) {
                if (typeof contextContainer !== 'undefined') {
                  contextContainer.hide();
                }
              });
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
