/**
 * @file
 *   Javascript for leaflet integration.
 */

/**
 * @param {String} drupalSettings.geolocation.google_map_url
 */

/**
 * @typedef {Object} LeafletMap
 * @property {Function} tileLayer
 * @property {Function} addTo
 * @property {Function} setView
 */


(function ($, Drupal) {
  'use strict';

  /* global L */

  /**
   * GeolocationLeafletMap element.
   *
   * @constructor
   * @augments {GeolocationMapBase}
   * @implements {GeolocationMapInterface}
   * @inheritDoc
   *
   * @prop {Object} settings.leaflet_settings - Leaflet specific settings.
   */
  function GeolocationLeafletMap(mapSettings) {
    this.type = 'leaflet';

    Drupal.geolocation.GeolocationMapBase.call(this, mapSettings);

    // Set the container size.
    this.container.css({
      height: this.settings.leaflet_settings.height,
      width: this.settings.leaflet_settings.width
    });
  }
  GeolocationLeafletMap.prototype = Object.create(Drupal.geolocation.GeolocationMapBase.prototype);
  GeolocationLeafletMap.prototype.constructor = GeolocationLeafletMap;
  GeolocationLeafletMap.prototype.initialize = function () {

    var leafletMap = L.map(this.container.get(0), {
      center: [this.lat, this.lng],
      zoom: this.settings.leaflet_settings.zoom
    });

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(leafletMap);

    /** @property {LeafletMap} leafletMap */
    this.leafletMap = leafletMap;
    this.loadedCallback(this, this.id);

    this.readyCallback();
  };
  GeolocationLeafletMap.prototype.setMapMarker = function (markerSettings) {
    if (markerSettings.setMarker === false) {
      return;
    }

    var currentMarker = L.marker([markerSettings.position.lat, markerSettings.position.lng], markerSettings).addTo(this.leafletMap);

    this.mapMarkers.push(currentMarker);

    return currentMarker;
  };


  Drupal.geolocation.GeolocationLeafletMap = GeolocationLeafletMap;

})(jQuery, Drupal);
