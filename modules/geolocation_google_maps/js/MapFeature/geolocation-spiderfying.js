/**
 * @typedef {Object} OverlappingMarkerSpiderfierInterface
 *
 * @property {function} addMarker
 * @property {string} markerStatus.SPIDERFIED
 * @property {string} markerStatus.UNSPIDERFIED
 * @property {string} markerStatus.SPIDERFIABLE
 * @property {string} markerStatus.UNSPIDERFIABLE
 */

/**
 * @typedef {Object} SpiderfyingSettings
 *
 * @extends {GeolocationMapFeatureSettings}
 *
 * @property {String} spiderfiable_marker_path
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Spiderfying.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationSpiderfying = {
    attach: function (context, drupalSettings) {
      Drupal.geolocation.executeFeatureOnAllMaps(
        'spiderfying',

        /**
         * @param {GeolocationGoogleMap} map - Current map.
         * @param {SpiderfyingSettings} featureSettings - Settings for current feature.
         */
        function (map, featureSettings) {
          if (typeof OverlappingMarkerSpiderfier === 'undefined') {
            return;
          }

          /* global OverlappingMarkerSpiderfier */

          map.addInitializedCallback(function(map) {

            var oms = null;

            /**
             * @type {OverlappingMarkerSpiderfierInterface} OverlappingMarkerSpiderfier
             */
            oms = new OverlappingMarkerSpiderfier(map.googleMap, {
              markersWontMove: true,
              keepSpiderfied: true
            });

            if (oms) {

              var geolocationOmsMarkerFunction  = function (marker) {
                google.maps.event.addListener(marker, 'spider_format', function (status) {

                  /**
                   * @param {Object} marker.originalIcon
                   */
                  if (typeof marker.originalIcon === 'undefined') {
                    var originalIcon = marker.getIcon();

                    if (typeof originalIcon === 'undefined') {
                      marker.orginalIcon = '';
                    }
                    else if (
                      typeof originalIcon !== 'undefined'
                      && originalIcon !== null
                      && typeof originalIcon.url !== 'undefined'
                      && originalIcon.url === featureSettings.spiderfiable_marker_path
                    ) {
                      // Do nothing.
                    }
                    else {
                      marker.orginalIcon = originalIcon;
                    }
                  }

                  var icon = null;
                  var iconSize = new google.maps.Size(23, 32);
                  switch (status) {
                    case OverlappingMarkerSpiderfier.markerStatus.SPIDERFIABLE:
                      icon = {
                        url: featureSettings.spiderfiable_marker_path,
                        size: iconSize,
                        scaledSize: iconSize
                      };
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.SPIDERFIED:
                      icon = marker.orginalIcon;
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.UNSPIDERFIABLE:
                      icon = marker.orginalIcon;
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.UNSPIDERFIED:
                      icon = marker.orginalIcon;
                      break;
                  }
                  marker.setIcon(icon);
                });

                $.each(
                  marker.listeners,
                  function (index, listener) {
                    if (listener.e === 'click') {
                      google.maps.event.removeListener(listener.listener);
                      marker.addListener('spider_click', listener.f);
                    }
                  }
                );
                oms.addMarker(marker);
              };

              $.each(map.mapMarkers, function(index, marker) {
                geolocationOmsMarkerFunction(marker);
              });

              map.addMarkerAddedCallback(
                function (marker) {
                  geolocationOmsMarkerFunction(marker)
                }
              );
            }
          });

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };

})(jQuery, Drupal);
