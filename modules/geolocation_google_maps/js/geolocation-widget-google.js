/**
 * @file
 *   Javascript for the Google geocoder widget.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Attach geocoder functionality.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches geocoder functionality to relevant elements.
   */
  Drupal.behaviors.geolocationGeocoderWidget = {
    attach: function (context, drupalSettings) {
      $('.geolocation-google-map-widget', context).each(function (index, item) {
        // TODO: Get map by ID from wrapper
          // TODO: get markers
        // TODO: Get form/elements (?)
        // TODO: Get delta from settings (?)

      });


      $.each(
        drupalSettings.geolocation.widgetSettings,
        function (mapId, widgetSetting) {

          /** @param {GeolocationGoogleMap} map */
          var map = Drupal.geolocation.getMapById(mapId);
          map.addLoadedCallback(function (map) {

            // If requested, also use location as value.
            if (typeof (widgetSetting.autoClientLocationMarker) !== 'undefined') {
              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (currentPosition) {
                  var currentLocation = new google.maps.LatLng(currentPosition.coords.latitude, currentPosition.coords.longitude);
                  map.setMapMarker({
                    position: currentLocation,
                    map: map.googleMap,
                    title: currentLocation.lat + ', ' + currentLocation.lng,
                    infoWindowContent: Drupal.t('Latitude') + ': ' + currentLocation.lat + ' ' + Drupal.t('Longitude') + ': ' + currentLocation.lng
                  });
                });
              }
            }

            // Execute when a location is defined by the widget.
            Drupal.geolocation.widget.addLocationCallback(function (location) {
              Drupal.geolocation.widget.setInputFields(location, map);
              map.removeMapMarkers();
              map.setMapMarker({
                position: location,
                map: map.googleMap,
                title: location.lat + ', ' + location.lng,
                infoWindowContent: Drupal.t('Latitude') + ': ' + location.lat + ' ' + Drupal.t('Longitude') + ': ' + location.lng
              });
            }, mapId);

            // Execute when a location is unset by the widget.
            Drupal.geolocation.widget.addClearCallback(function () {
              Drupal.geolocation.widget.clearInputFields(map);

              // Clear the map point.
              map.removeMapMarkers();
            }, mapId);

              // Add the click responders for setting the value.
            var singleClick;

            map.addClickCallback(function (e) {
              // Create 500ms timeout to wait for double click.
              singleClick = setTimeout(function () {
                Drupal.geolocation.widget.locationCallback({lat: Number(e.latLng.lat()), lng: Number(e.latLng.lng())}, map.id);
              }, 500);
            });

            map.addDoubleClickCallback(function (e) {
              clearTimeout(singleClick);
            });
          });
        }
      );
    }
  };

})(jQuery, Drupal);
