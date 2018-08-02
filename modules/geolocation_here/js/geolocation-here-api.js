/**
 * @file
 *   Javascript for HERE Maps integration.
 */

(function ($, Drupal) {
  'use strict';

  /* global H */

  /**
   * GeolocationHereMap element.
   *
   * @constructor
   * @augments {GeolocationMapBase}
   * @implements {GeolocationMapInterface}
   * @inheritDoc
   *
   * @prop {Object} settings.here_settings - HERE Maps specific settings.
   */
  function GeolocationHereMap(mapSettings) {
    if (typeof H === 'undefined') {
      console.error('HERE Maps library not loaded. Bailing out.'); // eslint-disable-line no-console
      return;
    }

    this.type = 'here';

    Drupal.geolocation.GeolocationMapBase.call(this, mapSettings);
    
    var defaultHereSettings = {
      zoom: 10
    };

    // Add any missing settings.
    this.settings.here_settings = $.extend(defaultHereSettings, this.settings.here_settings);

    // Set the container size.
    this.container.css({
      height: this.settings.here_settings.height,
      width: this.settings.here_settings.width
    });

    // Initialize the platform object:
    var platform = new H.service.Platform({
      app_id: drupalSettings.geolocation.hereMapsAppId,
      app_code: drupalSettings.geolocation.hereMapsAppCode,
      useHTTPS: true
    });

    // Obtain the default map types from the platform object
    var maptypes = platform.createDefaultLayers();

    // Instantiate (and display) a map object:
    this.hereMap = new H.Map(
      this.container.get(0),
      maptypes.normal.map,
      {
        zoom: this.settings.here_settings.zoom,
        center: { lng: this.lng, lat: this.lat }
      }
    );

    var behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(this.hereMap));

    this.addPopulatedCallback(function(map) {
      map.hereMap.addEventListener('tap', function(e) {
        var coord = map.hereMap.screenToGeo(e.currentPointer.viewportX, e.currentPointer.viewportY);
        map.clickCallback({lat: coord.lat, lng: coord.lng});
      });

      map.hereMap.addEventListener('contextmenu', function(e) {
        var coord = map.hereMap.screenToGeo(e.viewportX, e.viewportY);
        map.contextClickCallback({lat: coord.lat, lng: coord.lng});
      });
    });

    this.initializedCallback();
    this.populatedCallback();
  }
  GeolocationHereMap.prototype = Object.create(Drupal.geolocation.GeolocationMapBase.prototype);
  GeolocationHereMap.prototype.constructor = GeolocationHereMap;
  GeolocationHereMap.prototype.setZoom = function (zoom) {
    if (typeof zoom === 'undefined') {
      zoom = this.settings.here_settings.zoom;
    }
    zoom = parseInt(zoom);
    this.hereMap.setZoom(zoom);
  };
  GeolocationHereMap.prototype.setCenterByCoordinates = function (coordinates, accuracy, identifier) {
    Drupal.geolocation.GeolocationMapBase.prototype.setCenterByCoordinates.call(this, coordinates, accuracy, identifier);
    this.hereMap.setCenter(coordinates);
  };
  GeolocationHereMap.prototype.setMapMarker = function (markerSettings) {
    var hereMarkerSettings = {
      title: markerSettings.title
    }

    if (typeof markerSettings.icon === 'string') {
      hereMarkerSettings.icon = new H.map.Icon(markerSettings.icon);
    }

    var currentMarker = new H.map.Marker({ lat: parseFloat(markerSettings.position.lat), lng: parseFloat(markerSettings.position.lng) }, hereMarkerSettings);

    this.hereMap.addObject(currentMarker);

    currentMarker.locationWrapper = markerSettings.locationWrapper;

    Drupal.geolocation.GeolocationMapBase.prototype.setMapMarker.call(this, currentMarker);

    return currentMarker;
  };
  GeolocationHereMap.prototype.removeMapMarker = function (marker) {
    Drupal.geolocation.GeolocationMapBase.prototype.removeMapMarker.call(this, marker);
    this.hereMap.removeObject(marker);
  };
  GeolocationHereMap.prototype.fitMapToMarkers = function (locations) {

    locations = locations || this.mapMarkers;
    if (locations.length === 0) {
      return;
    }

    var group = new H.map.Group();
    group.addObjects(locations);

    this.hereMap.setViewBounds(group.getBounds());
  };

  Drupal.geolocation.GeolocationHereMap = GeolocationHereMap;
  Drupal.geolocation.addMapProvider('here', 'GeolocationHereMap');

})(jQuery, Drupal);
