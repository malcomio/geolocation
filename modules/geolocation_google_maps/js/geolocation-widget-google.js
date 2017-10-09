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

            if (typeof Drupal.geolocation.geocoderWidget.geocoder === 'undefined') {
              Drupal.geolocation.geocoderWidget.geocoder = new google.maps.Geocoder();
            }

            // Execute when a location is defined by the widget.
            Drupal.geolocation.geocoderWidget.addLocationCallback(function (location) {
              Drupal.geolocation.geocoderWidget.setInputFields(location, map);
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
            Drupal.geolocation.geocoderWidget.addClearCallback(function () {
              Drupal.geolocation.geocoderWidget.clearInputFields(map);
              map.controls.children('button.clear').addClass('disabled');
              // Clear the map point.
              map.removeMapMarkers();
            }, mapId);

            /**
             *
             * Initialize map.
             *
             */

            // If requested in settings, try to override map center by user location.
            if (typeof (widgetSetting.autoClientLocation) !== 'undefined') {
              if (
                widgetSetting.autoClientLocation
                && navigator.geolocation
                && !widgetSetting.locationSet
              ) {
                navigator.geolocation.getCurrentPosition(function (position) {
                  map.lat = position.coords.latitude;
                  map.lng = position.coords.longitude;

                  var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

                  map.addAccuracyIndicatorCircle(
                    location,
                    position.coords.accuracy
                  );

                  // If requested, also use location as value.
                  if (typeof (widgetSetting.autoClientLocationMarker) !== 'undefined') {
                    if (widgetSetting.autoClientLocationMarker) {
                      Drupal.geolocation.geocoderWidget.locationCallback(location, mapId);
                    }
                  }
                });
              }
            }

            /** @type {jQuery} controls */
            var controls = $('#geocoder-controls-wrapper-' + mapId, context);

            controls.children('input.location').first().autocomplete({
              autoFocus: true,
              source: function (request, response) {
                var autocompleteResults = [];
                Drupal.geolocation.geocoderWidget.geocoder.geocode(
                  {address: request.term},

                  /**
                   * Google Geocoding API geocode.
                   *
                   * @param {GoogleAddress[]} results - Returned results
                   * @param {String} status - Whether geocoding was successful
                   */
                  function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                      $.each(results, function (index, result) {
                        autocompleteResults.push({
                          value: result.formatted_address,
                          address: result
                        });
                      });
                    }
                    response(autocompleteResults);
                  }
                );
              },

              /**
               * Add the click listener.
               *
               * @param {object} event - Triggered event
               * @param {object} ui - Element from autoselect field.
               * @param {GoogleAddress} ui.item.address - Googleaddress bound to autoselect result.
               */
              select: function (event, ui) {
                // Set the map viewport.
                map.googleMap.fitBounds(ui.item.address.geometry.viewport);
                Drupal.geolocation.geocoderWidget.locationCallback(ui.item.address.geometry.location, mapId);
              }
            });

            controls.submit(function (e) {
              e.preventDefault();
              Drupal.geolocation.geocoderWidget.geocoder.geocode(
                {address: map.controls.children('input.location').first().val()},

                /**
                 * Google Geocoding API geocode.
                 *
                 * @param {GoogleAddress[]} results - Returned results
                 * @param {String} status - Whether geocoding was successful
                 */
                function (results, status) {
                  if (status === google.maps.GeocoderStatus.OK) {
                    map.googleMap.fitBounds(results[0].geometry.viewport);

                    Drupal.geolocation.geocoderWidget.locationCallback(results[0].geometry.location, mapId);
                  }
                }
              );
            });

            map.addControls(controls);

            google.maps.event.addDomListener(map.controls.children('button.search')[0], 'click', function (e) {
              e.preventDefault();
              Drupal.geolocation.geocoderWidget.geocoder.geocode(
                {address: controls.children('input.location').first().val()},

                /**
                 * Google Geocoding API geocode.
                 *
                 * @param {GoogleAddress[]} results - Returned results
                 * @param {String} status - Whether geocoding was successful
                 */
                function (results, status) {
                  if (status === google.maps.GeocoderStatus.OK) {
                    map.googleMap.fitBounds(results[0].geometry.viewport);

                    Drupal.geolocation.geocoderWidget.locationCallback(results[0].geometry.location, mapId);
                  }
                }
              );
            });

            google.maps.event.addDomListener(map.controls.children('button.clear')[0], 'click', function (e) {
              // Stop all that bubbling and form submitting.
              e.preventDefault();
              // Clear the input text.
              map.controls.children('input.location').val('');

              Drupal.geolocation.geocoderWidget.clearCallback(mapId);
            });

            // If the browser supports W3C Geolocation API.
            if (navigator.geolocation) {
              map.controls.children('button.locate').show();

              google.maps.event.addDomListener(map.controls.children('button.locate')[0], 'click', function (e) {
                // Stop all that bubbling and form submitting.
                e.preventDefault();

                // Get the geolocation from the browser.
                navigator.geolocation.getCurrentPosition(function (position) {
                  var newLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

                  map.addAccuracyIndicatorCircle(
                    newLocation,
                    position.coords.accuracy
                  );

                  map.setCenter(newLocation);

                  Drupal.geolocation.geocoderWidget.locationCallback(newLocation, mapId);
                });
              });
            }

            /**
             *
             * Final setup.
             *
             */

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
                Drupal.geolocation.geocoderWidget.locationCallback(e.latLng, map.id);
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
