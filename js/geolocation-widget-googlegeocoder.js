/**
 * @file
 *   Javascript for the Google geocoder widget.
 */

/**
 * @name GeocoderWidgetSettings
 * @property {String} autoClientLocation
 * @property {String} autoClientLocationMarker
 * @property {String} locationSet
 */

/**
 * @param {GeocoderWidgetSettings[]} drupalSettings.geolocation.widgetSettings
 */

/**
 * Callback for location found or set by widget.
 *
 * @callback geolocationGoogleGeocoderLocationCallback
 * @param {GoogleMapLatLng} location - Google address.
 */

/**
 * Callback for location unset by widget.
 *
 * @callback geolocationGoogleGeocoderClearCallback
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.geolocation.geocoderWidget = Drupal.geolocation.geocoderWidget || {};

  Drupal.geolocation.geocoderWidget.locationCallbacks = Drupal.geolocation.geocoderWidget.locationCallbacks || [];
  Drupal.geolocation.geocoderWidget.clearCallbacks = Drupal.geolocation.geocoderWidget.clearCallbacks || [];

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
      // Ensure itterables.
      drupalSettings.geolocation = drupalSettings.geolocation || {widgetSettings: []};
      $.each(
        drupalSettings.geolocation.widgetSettings,
        function (mapId, widgetSetting) {
          var map = Drupal.geolocation.getMapById(mapId);
          map.addLoadedCallback(function (map) {
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

                  map.drawAccuracyIndicator(
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

            /** @var {jQuery} controls */
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

                  map.drawAccuracyIndicator(
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

  /**
   * Provides the callback that is called when geocoderwidget defines a location.
   *
   * @param {GoogleMapLatLng} location - first returned address
   * @param {string} elementId - Source ID.
   */
  Drupal.geolocation.geocoderWidget.locationCallback = function (location, elementId) {
    // Ensure callbacks array;
    Drupal.geolocation.geocoderWidget.locationCallbacks = Drupal.geolocation.geocoderWidget.locationCallbacks || [];
    $.each(Drupal.geolocation.geocoderWidget.locationCallbacks, function (index, callbackContainer) {
      if (callbackContainer.elementId === elementId) {
        callbackContainer.callback(location);
      }
    });
  };

  /**
   * Adds a callback that will be called when a location is set.
   *
   * @param {geolocationGoogleGeocoderLocationCallback} callback - The callback
   * @param {string} elementId - Identify source of result by its element ID.
   */
  Drupal.geolocation.geocoderWidget.addLocationCallback = function (callback, elementId) {
    if (typeof elementId === 'undefined') {
      return;
    }
    Drupal.geolocation.geocoderWidget.locationCallbacks.push({callback: callback, elementId: elementId});
  };

  /**
   * Remove a callback that will be called when a location is set.
   *
   * @param {string} elementId - Identify the source
   */
  Drupal.geolocation.geocoderWidget.removeLocationCallback = function (elementId) {
    $.each(Drupal.geolocation.geocoderWidget.locationCallbacks, function (index, callback) {
      if (callback.elementId === elementId) {
        Drupal.geolocation.geocoderWidget.locationCallbacks.splice(index, 1);
      }
    });
  };

  /**
   * Provides the callback that is called when geocoderwidget unset the locations.
   *
   * @param {string} elementId - Source ID.
   */
  Drupal.geolocation.geocoderWidget.clearCallback = function (elementId) {
    // Ensure callbacks array;
    $.each(Drupal.geolocation.geocoderWidget.clearCallbacks, function (index, callbackContainer) {
      if (callbackContainer.elementId === elementId) {
        callbackContainer.callback(location);
      }
    });
  };

  /**
   * Adds a callback that will be called when a location is unset.
   *
   * @param {geolocationGoogleGeocoderClearCallback} callback - The callback
   * @param {string} elementId - Identify source of result by its element ID.
   */
  Drupal.geolocation.geocoderWidget.addClearCallback = function (callback, elementId) {
    if (typeof elementId === 'undefined') {
      return;
    }
    Drupal.geolocation.geocoderWidget.clearCallbacks.push({callback: callback, elementId: elementId});
  };

  /**
   * Remove a callback that will be called when a location is unset.
   *
   * @param {string} elementId - Identify the source
   */
  Drupal.geolocation.geocoderWidget.removeClearCallback = function (elementId) {
    $.each(Drupal.geolocation.geocoderWidget.clearCallbacks, function (index, callback) {
      if (callback.elementId === elementId) {
        Drupal.geolocation.geocoderWidget.clearCallbacks.splice(index, 1);
      }
    });
  };

  /**
   * Set the latitude and longitude values to the input fields
   *
   * @param {GoogleMapLatLng} latLng - A location (latLng) object from Google Maps API.
   * @param {Drupal.geolocation.GeolocationGoogleMap} map - The settings object that contains all of the necessary metadata for this map.
   */
  Drupal.geolocation.geocoderWidget.setInputFields = function (latLng, map) {
    // Update the lat and lng input fields.
    $('.canvas-' + map.id + ' .geolocation-hidden-lat').attr('value', latLng.lat());
    $('.canvas-' + map.id + ' .geolocation-hidden-lng').attr('value', latLng.lng());
  };

  /**
   * Set the latitude and longitude values to the input fields
   *
   * @param {GeolocationMap} map - The settings object that contains all of the necessary metadata for this map.
   */
  Drupal.geolocation.geocoderWidget.clearInputFields = function (map) {
    // Update the lat and lng input fields.
    $('.canvas-' + map.id + ' .geolocation-hidden-lat').attr('value', '');
    $('.canvas-' + map.id + ' .geolocation-hidden-lng').attr('value', '');
  };

})(jQuery, Drupal);
