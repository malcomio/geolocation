/**
 * @file
 * Marker Infobox for Bing map.
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

        function (map, featureSettings) {

          map.addMarkerAddedCallback(function (currentMarker) {

            if (typeof (currentMarker.locationWrapper) === 'undefined') {
              return;
            }

            // Generate a location pin.
            const pinLocation = new Microsoft.Maps.Location(currentMarker.position.lat, currentMarker.position.lng);
            let pin = new Microsoft.Maps.Pushpin(pinLocation);

            // Do we have any info to put into the infobox?
            var content = currentMarker.locationWrapper.find('.location-content');
            if (content.length) {
              content = content.html();

              var markerInfoWindow = {
                content: content.toString(),
                disableAutoPan: featureSettings.disableAutoPan
              };

              if (featureSettings.maxWidth > 0) {
                markerInfoWindow.maxWidth = featureSettings.maxWidth;
              }

              pin.metadata = {
                description: content.toString()
              };
              Microsoft.Maps.Events.addHandler(pin, 'click', pushpinClicked);
            }

            map.bingMap.entities.push(pin);

            function pushpinClicked(e) {
              // Make sure the infobox has metadata to display.
              if (e.target.metadata) {
                // Set the infobox options with the metadata of the pushpin.
                // TODO: sizing of the infobox.
                // TODO: recentre the map.
                Drupal.geolocation.currentInfoWindow.setOptions({
                  location: e.target.getLocation(),
                  title: e.target.metadata.title,
                  description: e.target.metadata.description,
                  visible: true
                });
              }
            }

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
