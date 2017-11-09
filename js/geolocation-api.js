/**
 * @file
 *   Javascript for the geolocation module.
 */

/**
 * @type {Object} drupalSettings.geolocation
 */

/**
 * @typedef {Object} GeolocationMapSettings
 *
 * @property {String} [type] Map type
 * @property {String} id
 * @property {Object} settings
 * @property {Number} lat
 * @property {Number} lng
 * @property {String} centreBehavior
 * @property {jQuery} wrapper
 * @property {GeolocationLocationSettings[]} mapMarkers
 */

/**
 * Callback when map is clicked.
 *
 * @callback GeolocationMapClickCallback
 * @param {Object} event - Click event.
 */

/**
 * Callback when map is right-clicked.
 *
 * @callback GeolocationMapContextClickCallback
 * @param {Object} event - Click event.
 */

/**
 * Callback when marker is clicked.
 *
 * @callback GeolocationMapMarkerClickCallback
 * @param {Object} event - Click event.
 */

/**
 * Callback when map provider becomes available.
 *
 * @callback GeolocationMapReadyCallback
 * @param {GeolocationMapInterface} map - Geolocation map.
 */

/**
 * Callback when map fully loaded.
 *
 * @callback GeolocationMapLoadedCallback
 * @param {GeolocationMapInterface} map - Geolocation map.
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
 * @property {jQuery} locationWrapper
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationMapInterface
 * @property {Boolean} ready - True when map provider available and readyCallbacks executed.
 * @property {Boolean} loaded - True when map fully loaded and all loadCallbacks executed.
 * @property {String} id
 * @property {GeolocationMapSettings} settings
 * @property {Number} lat
 * @property {Number} lng
 * @property {String} centreBehavior
 * @property {jQuery} wrapper
 * @property {jQuery} container
 * @property {Object[]} mapMarkers
 */

/**
 * Update existing map by settings.
 * @function
 * @name GeolocationMapInterface#addControl
 * @param {jQuery} element - Control element.
 * @param {string} [positionOnMap] - Control element positionOnMap.
 * @param {integer} [index] - Control element index.
 *
 * Update existing map by settings.
 * @function
 * @name GeolocationMapInterface#update
 * @param {GeolocationMapSettings} mapSettings - Settings to update by.
 *
 * Set marker on map.
 * @function
 * @name GeolocationMapInterface#setMapMarker
 * @param {GeolocationLocationSettings} Settings for the marker.
 * @return {Object} - Created marker.
 *
 * Remove all markers from map.
 * @function
 * @name GeolocationMapInterface#removeMapMarkers
 *
 * Center map by behavior.
 * @function
 * @name GeolocationMapInterface#setCenterByBehavior
 * @param {string} behavior - Behavior to center by.
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
 *
 * Executes {GeolocationMapClickCallbacks} for this map.
 * @function
 * @name GeolocationMapInterface#clickCallback
 *
 * Adds a callback that will be called when map is clicked.
 * @function
 * @name GeolocationMapInterface#addClickCallback
 * @param {GeolocationMapClickCallback} callback - Callback.
 *
 * Executes {GeolocationMapClickCallbacks} for this map.
 * @function
 * @name GeolocationMapInterface#doubleClickCallback
 *
 * Adds a callback that will be called on double click.
 * @function
 * @name GeolocationMapInterface#addDoubleClickCallback
 * @param {GeolocationMapClickCallback} callback - Callback.
 *
 * Executes {GeolocationMapMarkerClickCallbacks} for this map.
 * @function
 * @name GeolocationMapInterface#markerClickCallback
 *
 * Adds a callback that will be called when marker is clicked.
 * @function
 * @name GeolocationMapInterface#addMarkerClickCallback
 * @param {GeolocationMapMarkerClickCallback} callback - Callback.
 *
 * Executes {GeolocationMapContextClickCallbacks} for this map.
 * @function
 * @name GeolocationMapInterface#contextClickCallback
 *
 * Adds a callback that will be called when map is right-click.
 * @function
 * @name GeolocationMapInterface#addContextClickCallback
 * @param {GeolocationMapContextClickCallback} callback - Callback.
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
   */
  function GeolocationMapBase(mapSettings) {
    this.settings = mapSettings.settings || {};
    this.wrapper = mapSettings.wrapper;
    this.container = mapSettings.wrapper.find('.geolocation-map-container').first();
    this.ready = false;
    this.loaded = false;
    this.lat = mapSettings.lat;
    this.lng = mapSettings.lng;
    this.centreBehavior = mapSettings.centreBehavior;

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
    addControl: function (element, positionOnMap, index) {
      // Stub.
    },
    update: function (mapSettings) {
      this.settings = $.extend(this.settings, mapSettings.settings);
      this.wrapper = mapSettings.wrapper;
      this.container = mapSettings.wrapper.find('.geolocation-map-container').first();
      this.lat = mapSettings.lat;
      this.lng = mapSettings.lng;
    },
    setCenterByBehavior: function (centreBehavior) {
      centreBehavior = centreBehavior || this.centreBehavior;

      switch (centreBehavior) {
        case 'preset':
          this.addReadyCallback(function (map) {
            map.setCenterByCoordinates({
              lat: map.lat,
              lng: map.lng
            });
          });
          break;

        case 'fitlocations':
          this.addLoadedCallback(function (map) {
            map.fitMapToMarkers();
          });
          break;

        case 'fitboundaries':
          if (
            this.wrapper.data('centre-lat-north-east')
            && this.wrapper.data('centre-lng-north-east')
            && this.wrapper.data('centre-lat-south-west')
            && this.wrapper.data('centre-lng-south-west')
          ) {
            var centerBounds = {
              north: this.wrapper.data('centre-lat-north-east'),
              east: this.wrapper.data('centre-lng-north-east'),
              south: this.wrapper.data('centre-lat-south-west'),
              west: this.wrapper.data('centre-lng-south-west')
            };
            // Centre handling
            this.addReadyCallback(function (map) {
              map.fitBoundaries(centerBounds);
            });
          }
          break;

        case 'client_location':
          if (navigator.geolocation) {
            var that = this;
            navigator.geolocation.getCurrentPosition(function (position) {
              that.addLoadedCallback(function (map) {
                map.setCenterByCoordinates({lat: parseFloat(position.coords.latitude), lng: parseFloat(position.coords.longitude)}, parseInt(position.coords.accuracy));
              });
            });
          }
          break;
      }
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
    clickCallback: function (event) {
      this.clickCallbacks = this.clickCallbacks || [];
      $.each(this.clickCallbacks, function (index, callback) {
        callback(event);
      });
    },
    addClickCallback: function (callback) {
      this.clickCallbacks = this.clickCallbacks || [];
      this.clickCallbacks.push(callback);
    },
    doubleClickCallback: function (event) {
      this.doubleClickCallbacks = this.doubleClickCallbacks || [];
      $.each(this.doubleClickCallbacks, function (index, callback) {
        callback(event);
      });
    },
    addDoubleClickCallback: function (callback) {
      this.doubleClickCallbacks = this.doubleClickCallbacks || [];
      this.doubleClickCallbacks.push(callback);
    },
    contextClickCallback: function (event) {
      this.contextClickCallbacks = this.contextClickCallbacks || [];
      $.each(this.contextClickCallbacks, function (index, callback) {
        callback(event);
      });
    },
    addContextClickCallback: function (callback) {
      this.contextClickCallbacks = this.contextClickCallbacks || [];
      this.contextClickCallbacks.push(callback);
    },
    markerClickCallback: function (event) {
      this.markerClickCallbacks = this.markerClickCallbacks || [];
      $.each(this.markerClickCallbacks, function (index, callback) {
        callback(event);
      });
    },
    addMarkerClickCallback: function (callback) {
      this.markerClickCallbacks = this.markerClickCallbacks || [];
      this.markerClickCallbacks.push(callback);
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
      this.wrapper.find('.geolocation-location').each(function (index, locationWrapper) {

        /** @type {jQuery} */
        locationWrapper = $(locationWrapper);
        var position = {
          lat: Number(locationWrapper.data('lat')),
          lng: Number(locationWrapper.data('lng'))
        };

        /** @type {GeolocationLocationSettings} */
        var location = {
          position: position,
          title: locationWrapper.find('.location-title').text(),
          setMarker: true,
          locationWrapper: locationWrapper
        };

        if (typeof locationWrapper.data('icon') !== 'undefined') {
          location.icon = locationWrapper.data('icon');
        }

        if (typeof locationWrapper.data('label') !== 'undefined') {
          location.label = locationWrapper.data('label').toString();
        }

        if (locationWrapper.data('set-marker') === 'false') {
          location.setMarker = false;
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
   * @return {GeolocationMapInterface|boolean} Un-initialized map.
   */
  function Factory(mapSettings, reset) {
    reset = reset || false;
    mapSettings.type = mapSettings.type || 'google_maps';

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
      if (typeof Drupal.geolocation[Drupal.geolocation.MapProviders[mapSettings.type]] !== 'undefined') {
        var mapProvider = Drupal.geolocation[Drupal.geolocation.MapProviders[mapSettings.type]];
        map = new mapProvider(mapSettings);
      }
    }
    else {
      map = existingMap;
      map.update(mapSettings);
    }

    if (!map) {
      console.error("Map could not be initialzed"); // eslint-disable-line no-console
      return false;
    }

    map.setCenterByBehavior(mapSettings.centreBehavior);
    return map;
  }

  Drupal.geolocation.Factory = Factory;

  /**
   * @type {Object[]}
   */
  Drupal.geolocation.MapProviders = {};

  Drupal.geolocation.addMapProvider = function (type, name) {
    Drupal.geolocation.MapProviders[type] = name;
  };

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
