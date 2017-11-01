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
      $.each(
        drupalSettings.geolocation.widgetSettings,
        function (mapId, widgetSetting) {

          /** @param {GeolocationGoogleMap} map */
          var map = Drupal.geolocation.getMapById(mapId);
          map.addLoadedCallback(function (map) {

            if (typeof Drupal.geolocation.widget.geocoder === 'undefined') {
              Drupal.geolocation.widget.geocoder = new google.maps.Geocoder();
            }

            // Execute when a location is defined by the widget.
            Drupal.geolocation.widget.addLocationCallback(function (location) {
              Drupal.geolocation.widget.setInputFields(location, map);
              map.controls.children('button.clear').removeClass('disabled');
              map.removeMapMarkers();
              map.setMapMarker({
                position: location,
                map: map.googleMap,
                title: location.lat() + ', ' + location.lng(),
                infoWindowContent: Drupal.t('Latitude') + ': ' + location.lat() + ' ' + Drupal.t('Longitude') + ': ' + location.lng()
              });
            }, mapId);

            // Execute when a location is unset by the widget.
            Drupal.geolocation.widget.addClearCallback(function () {
              Drupal.geolocation.widget.clearInputFields(map);
              map.controls.children('button.clear').addClass('disabled');
              // Clear the map point.
              map.removeMapMarkers();
            }, mapId);

              // Add the click responders for setting the value.
            var singleClick;

            /**
             * Add the click listener.
             *
             * @param {GoogleMapLatLng} e.latLng
             */
            google.maps.event.addListener(map.googleMap, 'click', function (e) {
              // Create 500ms timeout to wait for double click.
              singleClick = setTimeout(function () {
                Drupal.geolocation.widget.locationCallback(e.latLng, map.id);
              }, 500);
            });

            // Add a doubleclick listener.
            google.maps.event.addListener(map.googleMap, 'dblclick', function (e) {
              clearTimeout(singleClick);
            });

            // Set the already processed flag.
            $(map.container).addClass('geolocation-processed');
          });
        }
      );
    }
  };

})(jQuery, Drupal);
