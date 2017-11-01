/**
 * @file
 *   Javascript for the map geocoder widget.
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
 * @callback geolocationGeocoderLocationCallback
 * @param {GeolocationCoordinates} location - Location.
 */

/**
 * Callback for location unset by widget.
 *
 * @callback geolocationGeocoderClearCallback
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.geolocation.widget = Drupal.geolocation.widget || {};

  Drupal.geolocation.widget.locationCallbacks = Drupal.geolocation.widget.locationCallbacks || [];
  Drupal.geolocation.widget.clearCallbacks = Drupal.geolocation.widget.clearCallbacks || [];

  /**
   * Provides the callback that is called when geocoderwidget defines a location.
   *
   * @param {GeolocationCoordinates} location - first returned address
   * @param {string} elementId - Source ID.
   */
  Drupal.geolocation.widget.locationCallback = function (location, elementId) {
    // Ensure callbacks array;
    Drupal.geolocation.widget.locationCallbacks = Drupal.geolocation.widget.locationCallbacks || [];
    $.each(Drupal.geolocation.widget.locationCallbacks, function (index, callbackContainer) {
      if (callbackContainer.elementId === elementId) {
        callbackContainer.callback(location);
      }
    });
  };

  /**
   * Adds a callback that will be called when a location is set.
   *
   * @param {geolocationGeocoderLocationCallback} callback - The callback
   * @param {string} elementId - Identify source of result by its element ID.
   */
  Drupal.geolocation.widget.addLocationCallback = function (callback, elementId) {
    if (typeof elementId === 'undefined') {
      return;
    }
    Drupal.geolocation.widget.locationCallbacks.push({callback: callback, elementId: elementId});
  };

  /**
   * Remove a callback that will be called when a location is set.
   *
   * @param {string} elementId - Identify the source
   */
  Drupal.geolocation.widget.removeLocationCallback = function (elementId) {
    $.each(Drupal.geolocation.widget.locationCallbacks, function (index, callback) {
      if (callback.elementId === elementId) {
        Drupal.geolocation.widget.locationCallbacks.splice(index, 1);
      }
    });
  };

  /**
   * Provides the callback that is called when geocoderwidget unset the locations.
   *
   * @param {string} elementId - Source ID.
   */
  Drupal.geolocation.widget.clearCallback = function (elementId) {
    // Ensure callbacks array;
    $.each(Drupal.geolocation.widget.clearCallbacks, function (index, callbackContainer) {
      if (callbackContainer.elementId === elementId) {
        callbackContainer.callback(location);
      }
    });
  };

  /**
   * Adds a callback that will be called when a location is unset.
   *
   * @param {geolocationGeocoderClearCallback} callback - The callback
   * @param {string} elementId - Identify source of result by its element ID.
   */
  Drupal.geolocation.widget.addClearCallback = function (callback, elementId) {
    if (typeof elementId === 'undefined') {
      return;
    }
    Drupal.geolocation.widget.clearCallbacks.push({callback: callback, elementId: elementId});
  };

  /**
   * Remove a callback that will be called when a location is unset.
   *
   * @param {string} elementId - Identify the source
   */
  Drupal.geolocation.widget.removeClearCallback = function (elementId) {
    $.each(Drupal.geolocation.widget.clearCallbacks, function (index, callback) {
      if (callback.elementId === elementId) {
        Drupal.geolocation.widget.clearCallbacks.splice(index, 1);
      }
    });
  };

  /**
   * Set the latitude and longitude values to the input fields
   *
   * @param {GeolocationCoordinates} latLng - A location (latLng) object.
   * @param {GeolocationMapInterface} map - The settings object that contains all of the necessary metadata for this map.
   */
  Drupal.geolocation.widget.setInputFields = function (latLng, map) {
    // Update the lat and lng input fields.
    $('.canvas-' + map.id + ' .geolocation-hidden-lat').attr('value', latLng.lat);
    $('.canvas-' + map.id + ' .geolocation-hidden-lng').attr('value', latLng.lng);
  };

  /**
   * Set the latitude and longitude values to the input fields
   *
   * @param {GeolocationMapInterface} map - The settings object that contains all of the necessary metadata for this map.
   */
  Drupal.geolocation.widget.clearInputFields = function (map) {
    // Update the lat and lng input fields.
    $('.canvas-' + map.id + ' .geolocation-hidden-lat').attr('value', '');
    $('.canvas-' + map.id + ' .geolocation-hidden-lng').attr('value', '');
  };

})(jQuery, Drupal);
