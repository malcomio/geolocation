/**
 * @file
 * Javascript for the Google map formatter.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Find and display all maps.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches Google Maps formatter functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMap = {
    attach: function (context, drupalSettings) {
      $('.geolocation-map-wrapper', context).each(function (index, item) {
        var mapWrapper = $(item);
        var mapSettings = {};
        var centreBehavior = 'fitlocations';
        mapSettings.id = mapWrapper.attr('id');
        mapSettings.wrapper = mapWrapper;

        if (
          mapWrapper.length
          && !mapWrapper.hasClass('geolocation-processed')
        ) {
          mapSettings.lat = 0;
          mapSettings.lng = 0;

          if (
            mapWrapper.data('centre-lat')
            && mapWrapper.data('centre-lng')
          ) {
            mapSettings.lat = Number(mapWrapper.data('centre-lat'));
            mapSettings.lng = Number(mapWrapper.data('centre-lng'));
          }

          if (mapWrapper.data('map-type')) {
            mapSettings.type = mapWrapper.data('map-type');
          }
          else {
            mapSettings.type = 'google';
          }

          if (mapWrapper.data('centre-behavior')) {
            centreBehavior = mapWrapper.data('centre-behavior');
          }

          $.each(drupalSettings.geolocation.maps, function (index, currentSettings) {
            if (currentSettings.id === mapSettings.id) {
              mapSettings = $.extend(currentSettings, mapSettings);
            }
          });

          var map = Drupal.geolocation.Factory(mapSettings);
          map.initialize();

          map.addReadyCallback(function (map) {

            /**
             * Result handling.
             */
            map.removeMapMarkers();

            var locations = map.loadMarkersFromContainer();
            $.each(locations, function (index, location) {
              map.setMapMarker(location);
            });

            // Set the already processed flag.
            map.container.addClass('geolocation-processed');
          });

          switch (centreBehavior) {
            case 'fitlocations':
              map.addLoadedCallback(function (map) {
                map.fitMapToMarkers();
              });
              break;

            case 'fitboundaries':
              if (
                mapWrapper.data('centre-lat-north-east')
                && mapWrapper.data('centre-lng-north-east')
                && mapWrapper.data('centre-lat-south-west')
                && mapWrapper.data('centre-lng-south-west')
              ) {
                var centerBounds = {
                  north: mapWrapper.data('centre-lat-north-east'),
                  east: mapWrapper.data('centre-lng-north-east'),
                  south: mapWrapper.data('centre-lat-south-west'),
                  west: mapWrapper.data('centre-lng-south-west')
                };
                // Centre handling
                map.addReadyCallback(function (map) {
                  map.fitBoundaries(centerBounds);
                });
              }
              break;

            case 'html5':
              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                  map.addLoadedCallback(function (map) {
                    map.setCenterByCoordinates({lat: parseFloat(position.coords.latitude), lng: parseFloat(position.coords.longitude)}, parseInt(position.coords.accuracy));
                  });
                });
              }
              break;


          }
        }
      });
    }
  };

})(jQuery, Drupal);
