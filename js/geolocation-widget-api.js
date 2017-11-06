/**
 * @file
 *   Javascript for the map geocoder widget.
 */

/**
 * @name GeolocationMapWidgetSettings
 * @property {String} autoClientLocationMarker
 */

/**
 * Callback for location found or set by widget.
 *
 * @callback geolocationGeocoderLocationCallback
 * @param {GeolocationCoordinates} location - Location.
 * @param {int} delta - Delta.
 */

/**
 * Callback for location unset by widget.
 *
 * @callback geolocationGeocoderClearCallback
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationMapWidgetInterface
 * @property {GeolocationMapWidgetSettings} settings
 * @property {jQuery} wrapper
 * @property {jQuery} container
 * @property {Object[]} mapMarkers
 * @property {geolocationGeocoderLocationCallback[]} locationAddedCallbacks
 * @property {geolocationGeocoderLocationCallback[]} locationModifiedCallbacks
 * @property {geolocationGeocoderLocationCallback[]} locationRemovedCallbacks
 * @property {geolocationGeocoderClearCallback[]} clearCallbacks
 */

/**
 * @function
 * @name GeolocationMapWidgetInterface#locationAddedCallback
 * @param {GeolocationCoordinates} location - first returned address
 *
 * Adds a callback that will be called when a location is set.
 * @function
 * @name GeolocationMapWidgetInterface#addLocationAddedCallback
 * @param {geolocationGeocoderLocationCallback} callback - The callback
 *
 * Get map marker by delta.
 * @function
 * @name GeolocationMapWidgetInterface#getMarkerByDelta
 * @param {int} delta - Delta
 *
 * Get map input by delta.
 * @function
 * @name GeolocationMapWidgetInterface#getInputByDelta
 * @param {int} delta - Delta
 *
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.geolocation.widget = Drupal.geolocation.widget || {};

  /**
   * Geolocation map widget.
   *
   * @constructor
   * @abstract
   * @implements {GeolocationMapWidgetInterface}
   * @param {string} id - ID.
   * @param {GeolocationMapWidgetSettings} widgetSettings - Setting to create map.
   */
  function GeolocationMapWidgetBase(id) {

    this.locationAddedCallbacks = [];
    this.locationModifiedCallbacks = [];
    this.locationRemovedCallbacks = [];

    this.clearCallbacks = [];

    this.settings = widgetSettings || {};
    this.wrapper = widgetSettings.wrapper;

    this.map = widgetSettings.wrapper;

    Drupal.geolocation.widgets.push(this);

    return this;
  }

  GeolocationMapWidgetBase.prototype = {

    locationAddedCallback: function (location, delta) {
      $.each(this.locationAddedCallbacks, function (index, callback) {
        callback(location, delta);
      });
    },

    addLocationAddedCallback: function (callback) {
      this.locationAddedCallbacks.push(callback);
    },

    getMarkerByDelta: function (delta) {

    },

    getInputByDelta: function (delta) {

    },

    setMarker: function (location, delta) {

    },

    setInput: function (location, delta) {

    }

  };

  Drupal.geolocation.widget.GeolocationMapWidgetBase = GeolocationMapWidgetBase;

})(jQuery, Drupal);
