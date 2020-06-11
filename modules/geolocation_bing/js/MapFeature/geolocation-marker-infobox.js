/**
 * @file
 * Marker InfoWindow.
 */

/**
 * @typedef {Object} MarkerInfoWindowSettings
 *
 * @extends {GeolocationMapFeatureSettings}
 *
 * @property {Boolean} infoAutoDisplay
 * @property {Boolean} disableAutoPan
 * @property {Boolean} infoWindowSolitary
 * @property {int} maxWidth
 */

/**
 * @typedef {Object} GoogleInfoWindow
 * @property {Function} open
 * @property {Function} close
 */

/**
 * @property {GoogleInfoWindow} GeolocationGoogleMap.infoWindow
 * @property {function({}):GoogleInfoWindow} GeolocationGoogleMap.InfoWindow
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

        /**
         * @param {GeolocationGoogleMap} map - Current map.
         * @param {MarkerInfoWindowSettings} featureSettings - Settings for current feature.
         */
        function (map, featureSettings) {


          map.addMarkerAddedCallback(function (currentMarker) {

            if (typeof (currentMarker.locationWrapper) === 'undefined') {
              return;
            }


            var content = currentMarker.locationWrapper.find('.location-content');

            if (content.length < 1) {
              return;
            }
            content = content.html();

            var markerInfoWindow = {
              content: content.toString(),
              disableAutoPan: featureSettings.disableAutoPan
            };

            if (featureSettings.maxWidth > 0) {
              markerInfoWindow.maxWidth = featureSettings.maxWidth;
            }

            // Set the info popup text.
            var currentInfoWindow = new Microsoft.Maps.Infobox(map.getCenter(), {
              visible: false
            });

            currentInfoWindow.setMap(map.bingMap);

            const pinLocation = new Microsoft.Maps.Location(currentMarker.position.lat, currentMarker.position.lng);

            let pin = new Microsoft.Maps.Pushpin(pinLocation);

            pin.metadata = {
              description: content.toString()
            };
            Microsoft.Maps.Events.addHandler(pin, 'click', pushpinClicked);

            map.bingMap.entities.push(pin);


            function pushpinClicked(e) {
              // Make sure the infobox has metadata to display.
              if (e.target.metadata) {
                // Set the infobox options with the metadata of the pushpin.
                // TODO: sizing of the infobox.
                // TODO: recentre the map.
                currentInfoWindow.setOptions({
                  location: e.target.getLocation(),
                  title: e.target.metadata.title,
                  description: e.target.metadata.description,
                  visible: true
                });
              }
            }


            // currentMarker.addListener('click', function () {
            //   if (featureSettings.infoWindowSolitary) {
            //     if (typeof map.infoWindow !== 'undefined') {
            //       map.infoWindow.close();
            //     }
            //     map.infoWindow = currentInfoWindow;
            //   }
            //   currentInfoWindow.open(map.bingMap, currentMarker);
            // });

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
