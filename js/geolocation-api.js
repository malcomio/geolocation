/**
 * @file
 *   Javascript for the geolocation module.
 */

/**
 * @type {Object} drupalSettings.geolocation
 */

/**
 * @typedef {Object} GeolocationMapSettings
 * @property {String} [type] Map type
 * @property {String} id
 * @property {Object} settings
 * @property {Number} lat
 * @property {Number} lng
 * @property {jQuery} wrapper
 * @property {Object[]} mapMarkers
 * @property {GoogleInfoWindow} infoWindow
 */

/**
 * Callback when map provider becomes available.
 *
 * @callback GeolocationMapReadyCallback
 * @param {GeolocationMapInterface} map - Google map.
 */

/**
 * Callback when map fully loaded.
 *
 * @callback GeolocationMapLoadedCallback
 * @param {GeolocationMapInterface} map - Google map.
 */

/**
 * @typedef {Object} GeolocationCoordinates
 *
 * @property {Number} lat
 * @property {Number} lng
 */

/**
 * @typedef {Object} GeolocationLocationSettings
 *
 * @property {GeolocationCoordinates} position
 * @property {string} title
 * @property {boolean} [setMarker]
 * @property {string} [icon]
 * @property {string} [label]
 * @property {string} [infoWindowContent]
 * @property {boolean} [infoWindowSolitary]
 * @property {boolean} [skipInfoWindow]
 * @property {boolean} [setMarker]
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationMapInterface
 * @property {Boolean} ready - True when map provider available and readyCallbacks executed.
 * @property {Boolean} loaded - True when map fully loaded and all loadCallbacks executed.
 */

/**
 * Update existing map by settings.
 * @function
 * @name GeolocationMapInterface#update
 * @param {GeolocationMapSettings} mapSettings - Settings to update by.
 *
 * Set marker on map.
 * @function
 * @name GeolocationMapInterface#setMapMarker
 * @param {GeolocationLocationSettings} Settings for the marker.
 * @param {Boolean} [skipInfoWindow=false] - Skip attaching InfoWindow.
 * @return {Object} - Created marker.
 *
 * Remove all markers from map.
 * @function
 * @name GeolocationMapInterface#removeMapMarkers
 *
 * Center map on coordinates.
 * @function
 * @name GeolocationMapInterface#setCenterByCoordinates
 * @param {GeolocationCoordinates} coordinates - Coordinates to center on.
 * @param {Number} [accuracy] - Optional accuracy in meter.
 *
 * Fit map to markers.
 * @function
 * @name GeolocationMapInterface#fitMapToMarkers
 * @param {GeolocationLocationSettings[]} [locations] Override using map.mapMarker.
 *
 * Fit map to bounds.
 * @function
 * @name GeolocationMapInterface#fitBoundaries
 * @param {Object} boundaries - Override using map.mapMarker.
 *
 * Executes {GeolocationMapLoadedCallback[]} for this map.
 * @function
 * @name GeolocationMapInterface#loadedCallback
 *
 * Adds a callback that will be called when map is fully loaded.
 * @function
 * @name GeolocationMapInterface#addLoadedCallback
 * @param {GeolocationMapLoadedCallback} callback - Callback.
 *
 * Executes {GeolocationMapReadyCallbacks} for this map.
 * @function
 * @name GeolocationMapInterface#readyCallback
 *
 * Adds a callback that will be called when map provider becomes available.
 * @function
 * @name GeolocationMapInterface#addReadyCallback
 * @param {GeolocationMapReadyCallback} callback - Callback.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * @namespace
   * @prop {Object} Drupal.geolocation
   */
  Drupal.geolocation = Drupal.geolocation || {};

  /**
   * @type {GeolocationMapInterface[]}
   * @prop {GeolocationMapSettings} settings The map settings.
   */
  Drupal.geolocation.maps = Drupal.geolocation.maps || [];

  /**
   * Geolocation map.
   *
   * @constructor
   * @abstract
   * @implements {GeolocationMapInterface}
   * @param {GeolocationMapSettings} mapSettings Setting to create map.
   * @prop {String} id ID
   * @prop {jQuery} container Wrapping element.
   * @prop {Object} settings Settings element.
   */
  function GeolocationMapBase(mapSettings) {
    this.settings = mapSettings.settings || {};
    this.wrapper = mapSettings.wrapper;
    this.container = mapSettings.wrapper.find('.geolocation-map-container').first();
    this.ready = false;
    this.loaded = false;
    this.lat = mapSettings.lat;
    this.lng = mapSettings.lng;

    if (typeof mapSettings.id === 'undefined') {
      this.id = 'map' + Math.floor(Math.random() * 10000);
    }
    else {
      this.id = mapSettings.id;
    }

    this.mapMarkers = this.mapMarkers || [];

    Drupal.geolocation.maps.push(this);

    return this;
  }

  GeolocationMapBase.prototype = {
    update: function (mapSettings) {
      this.settings = $.extend(this.settings, mapSettings.settings);
      this.wrapper = mapSettings.wrapper;
      this.container = mapSettings.wrapper.find('.geolocation-map-container').first();
      this.lat = mapSettings.lat;
      this.lng = mapSettings.lng;
    },
    setCenterByCoordinates: function (coordinates, accuracy) {
      // Stub.
    },
    setMapMarker: function (markerSettings) {
      // Stub.
    },
    removeMapMarkers: function () {
      // Stub.
    },
    fitMapToMarkers: function () {
      // Stub.
    },
    fitBoundaries: function (boundaries) {
      // Stub.
    },
    readyCallback: function () {
      this.readyCallbacks = this.readyCallbacks || [];
      var that = this;
      $.each(this.readyCallbacks, function (index, callback) {
        callback(that);
      });
      this.readyCallbacks = [];
      this.ready = true;
    },
    addReadyCallback: function (callback) {
      if (this.ready) {
        callback(this);
      }
      else {
        this.readyCallbacks = this.readyCallbacks || [];
        this.readyCallbacks.push(callback);
      }
    },
    loadedCallback: function () {
      this.loadedCallbacks = this.loadedCallbacks || [];
      var that = this;
      $.each(this.loadedCallbacks, function (index, callback) {
        callback(that);
      });
      this.loadedCallbacks = [];
      this.loaded = true;
    },
    addLoadedCallback: function (callback) {
      if (this.loaded) {
        callback(this);
      }
      else {
        this.loadedCallbacks = this.loadedCallbacks || [];
        this.loadedCallbacks.push(callback);
      }
    },
    loadMarkersFromContainer: function () {
      var locations = [];
      this.wrapper.find('.geolocation-map-locations .geolocation-location').each(function (index, locationWrapper) {

        /** @type {jQuery} */
        locationWrapper = $(locationWrapper);
        var position = {
          lat: Number(locationWrapper.data('lat')),
          lng: Number(locationWrapper.data('lng'))
        };

        /** @type {GeolocationLocationSettings} */
        var location = {
          position: position,
          title: locationWrapper.children('.location-title').text(),
          infoWindowContent: locationWrapper.html(),
          infoWindowSolitary: true,
          setMarker: true,
          skipInfoWindow: false
        };

        if (typeof locationWrapper.data('icon') !== 'undefined') {
          location.icon = locationWrapper.data('icon');
        }

        if (typeof locationWrapper.data('markerLabel') !== 'undefined') {
          location.label = locationWrapper.data('markerLabel').toString();
        }

        if (locationWrapper.data('set-marker') === 'false') {
          location.setMarker = false;
        }

        if (locationWrapper.children('.location-content').text().trim().length < 1) {
          location.skipInfoWindow = true;
        }

        locations.push(location);
      });

      return locations;
    }
  };

  Drupal.geolocation.GeolocationMapBase = GeolocationMapBase;

  /**
   * Factory creating map instances.
   *
   * @constructor
   * @param {GeolocationMapSettings} mapSettings The map settings.
   * @param {Boolean} [reset] Force creation of new map.
   * @return {GeolocationMapInterface} Un-initialized map.
   */
  function Factory(mapSettings, reset) {
    reset = reset || false;
    mapSettings.type = mapSettings.type || 'google';

    var map = null;

    /**
     * Previously stored map.
     * @type {boolean|GeolocationMapInterface}
     */
    var existingMap = false;

    $.each(Drupal.geolocation.maps, function (index, map) {
      if (map.id === mapSettings.id) {
        existingMap = Drupal.geolocation.maps[index];
      }
    });

    if (reset === true || !existingMap) {
      // TODO: Is there some Plugin mechanism in JS? Ideally the Factory should not need to know how to do load things.
      switch (mapSettings.type) {
        case 'google':
          map = new Drupal.geolocation.GeolocationGoogleMap(mapSettings);
          break;

        case 'leaflet':
          map = new Drupal.geolocation.GeolocationLeafletMap(mapSettings);
          break;
      }
    }
    else {
      map = existingMap;
      map.update(mapSettings);
    }
    return map;
  }

  Drupal.geolocation.Factory = Factory;

  /**
   * Get map by ID.
   *
   * @param {String} id - Map ID to retrieve.
   * @return {GeolocationMapInterface|boolean} - Retrieved map or false.
   */
  Drupal.geolocation.getMapById = function (id) {
    var map = false;
    $.each(Drupal.geolocation.maps, function (index, currentMap) {
      if (currentMap.id === id) {
        map = currentMap;
      }
    });
    return map;
  };

})(jQuery, Drupal);
