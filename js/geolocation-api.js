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
 * @property {GeolocationMapMarker[]} mapMarkers
 */

/**
 * Callback when map is clicked.
 *
 * @callback GeolocationMapClickCallback
 * @param {GeolocationCoordinates} location - Click location.
 */

/**
 * Callback when a marker is added or removed.
 *
 * @callback GeolocationMarkerCallback
 * @param {GeolocationMapMarker} marker - Map marker.
 */

/**
 * Callback when map is right-clicked.
 *
 * @callback GeolocationMapContextClickCallback
 * @param {GeolocationCoordinates} location - Click location.
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

 * @property {Number} lat
 * @property {Number} lng
 */

/**
 * @typedef {Object} GeolocationMapMarker
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
 *
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
 *
 * @property {function({jQuery}):{jQuery}} addControl - Add control to map, identified by classes.
 * @property {function()} removeControls - Remove controls from map.
 *
 * @property {function()} loadedCallback - Executes {GeolocationMapLoadedCallback[]} for this map.
 * @property {function({GeolocationMapLoadedCallback})} addLoadedCallback - Adds a callback that will be called when map is fully loaded.
 * @property {function()} readyCallback - Executes {GeolocationMapReadyCallbacks[]} for this map.
 * @property {function({GeolocationMapReadyCallback})} addReadyCallback - Adds a callback that will be called when map provider becomes available.
 * @property {function({GeolocationMapSettings})} update - Update existing map by settings.
 *
 * @property {function({GeolocationMapMarker}):{GeolocationMapMarker}} setMapMarker - Set marker on map.
 * @property {function({GeolocationMapMarker})} removeMapMarker - Remove single marker.
 * @property {function()} removeMapMarkers - Remove all markers from map.
 *
 * @property {function({string})} setCenterByBehavior - Center map by behavior.
 * @property {function({GeolocationCoordinates}, [{Number}])} setCenterByCoordinates - Center map on coordinates.
 * @property {function([{GeolocationMapMarker[]}])} fitMapToMarkers - Fit map to markers.
 * @property {function({Object})} fitBoundaries - Fit map to bounds.
 *
 * @property {function({Event})} clickCallback - Executes {GeolocationMapClickCallbacks} for this map.
 * @property {function({GeolocationMapClickCallback})} addClickCallback - Adds a callback that will be called when map is clicked.
 *
 * @property {function({Event})} doubleClickCallback - Executes {GeolocationMapClickCallbacks} for this map.
 * @property {function({GeolocationMapClickCallback})} addDoubleClickCallback - Adds a callback that will be called on double click.
 *
 * @property {function({Event})} contextClickCallback - Executes {GeolocationMapContextClickCallbacks} for this map.
 * @property {function({GeolocationMapContextClickCallback})} addContextClickCallback - Adds a callback that will be called when map is clicked.
 *
 * @property {function({GeolocationMapMarker})} markerAddedCallback - Executes {GeolocationMarkerCallback} for this map.
 * @property {function({GeolocationMarkerCallback})} addMarkerAddedCallback - Adds a callback that will be called on marker(s) being added.
 *
 * @property {function({GeolocationMapMarker})} markerRemoveCallback - Executes {GeolocationMarkerCallback} for this map.
 * @property {function({GeolocationMarkerCallback})} addMarkerRemoveCallback - Adds a callback that will be called before marker is removed.
 *
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

    if (this.container.length !== 1) {
      throw "Geolocation - Map container not found";
    }

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

    return this;
  }

  GeolocationMapBase.prototype = {
    addControl: function (element) {
      // Stub.
    },
    removeControls: function () {
      // Stub.
    },
    update: function (mapSettings) {
      this.settings = $.extend(this.settings, mapSettings.settings);
      this.wrapper = mapSettings.wrapper;
      this.container = mapSettings.wrapper.find('.geolocation-map-container').first();
      this.lat = mapSettings.lat;
      this.lng = mapSettings.lng;
      this.centreBehavior = mapSettings.centreBehavior;
    },
    setCenterByBehavior: function (centreBehavior) {
      centreBehavior = centreBehavior || this.centreBehavior;

      switch (centreBehavior) {
        case 'preserve':
          break;

        case 'preset':
          this.setCenterByCoordinates({
            lat: this.lat,
            lng: this.lng
          });
          break;

        case 'fitlocations':
          this.fitMapToMarkers();
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
            this.fitBoundaries(centerBounds);
          }
          break;

        case 'client_location':
          if (navigator.geolocation) {
            var that = this;
            var successCallback = function (position) {
              that.setCenterByCoordinates({lat: position.coords.latitude, lng: position.coords.longitude}, position.coords.accuracy);
            };
            navigator.geolocation.getCurrentPosition(successCallback);
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
    removeMapMarker: function (marker) {
      var that = this;
      $.each(
        this.mapMarkers,

        /**
         * @param {integer} index - Current index.
         * @param {GeolocationMapMarker} item - Current marker.
         */
        function (index, item) {
          if (item === marker) {
            that.markerRemoveCallback(marker);
            that.mapMarkers.splice(Number(index), 1);
          }
        }
      );
    },
    removeMapMarkers: function () {
      var that = this;
      var shallowCopy = $.extend({}, this.mapMarkers);
      $.each(
        shallowCopy,

        /**
         * @param {integer} index - Current index.
         * @param {GeolocationMapMarker} item - Current marker.
         */
        function (index, item) {
          if (typeof item === 'undefined') {
            return;
          }
          that.removeMapMarker(item);
        }
      );
    },
    fitMapToMarkers: function () {
      // Stub.
    },
    fitBoundaries: function (boundaries) {
      // Stub.
    },
    clickCallback: function (location) {
      this.clickCallbacks = this.clickCallbacks || [];
      $.each(this.clickCallbacks, function (index, callback) {
        callback(location);
      });
    },
    addClickCallback: function (callback) {
      this.clickCallbacks = this.clickCallbacks || [];
      this.clickCallbacks.push(callback);
    },
    doubleClickCallback: function (location) {
      this.doubleClickCallbacks = this.doubleClickCallbacks || [];
      $.each(this.doubleClickCallbacks, function (index, callback) {
        callback(location);
      });
    },
    addDoubleClickCallback: function (callback) {
      this.doubleClickCallbacks = this.doubleClickCallbacks || [];
      this.doubleClickCallbacks.push(callback);
    },
    contextClickCallback: function (location) {
      this.contextClickCallbacks = this.contextClickCallbacks || [];
      $.each(this.contextClickCallbacks, function (index, callback) {
        callback(location);
      });
    },
    addContextClickCallback: function (callback) {
      this.contextClickCallbacks = this.contextClickCallbacks || [];
      this.contextClickCallbacks.push(callback);
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
    markerAddedCallback: function (marker) {
      this.markerAddedCallbacks = this.markerAddedCallbacks || [];
      $.each(this.markerAddedCallbacks, function (index, callback) {
        callback(marker);
      });
    },
    addMarkerAddedCallback: function (callback) {
      this.markerAddedCallbacks = this.markerAddedCallbacks || [];
      this.markerAddedCallbacks.push(callback);
    },
    markerRemoveCallback: function (marker) {
      this.markerRemoveCallbacks = this.markerRemoveCallbacks || [];
      $.each(this.markerRemoveCallbacks, function (index, callback) {
        callback(marker);
      });
    },
    addMarkerRemoveCallback: function (callback) {
      this.markerRemoveCallbacks = this.markerRemoveCallbacks || [];
      this.markerRemoveCallbacks.push(callback);
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
      this.wrapper.find('.geolocation-location').each(function (index, locationWrapperElement) {

        var locationWrapper = $(locationWrapperElement);

        /** {GeolocationCoordinates */
        var position = {
          lat: Number(locationWrapper.data('lat')),
          lng: Number(locationWrapper.data('lng'))
        };

        /** @type {GeolocationMapMarker} */
        var location = {
          position: position,
          title: locationWrapper.find('.location-title').text().trim(),
          setMarker: true,
          locationWrapper: locationWrapper
        };

        if (typeof locationWrapper.data('icon') !== 'undefined') {
          location.icon = locationWrapper.data('icon').toString();
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
        Drupal.geolocation.maps.push(map);
      }
    }
    else {
      map = existingMap;
      map.update(mapSettings);
    }

    if (!map) {
      console.error("Map could not be initialzed."); // eslint-disable-line no-console
      return false;
    }

    if (map.container.length !== 1) {
      console.error("Map container went away."); // eslint-disable-line no-console
      return false;
    }

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

    if (!map || map.container.length !== 1) {
      console.error("Existing map could not be retrieved."); // eslint-disable-line no-console
      return false;
    }

    return map;
  };

})(jQuery, Drupal);
