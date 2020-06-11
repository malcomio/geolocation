/**
 * @file
 * Javascript for Bing maps integration.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationBingMap element.
   *
   * @constructor
   * @augments {GeolocationMapBase}
   * @implements {GeolocationMapInterface}
   * @inheritDoc
   *
   * @prop {Map} bingMap
   * @prop {L.LayerGroup} markerLayer
   * @prop {TileLayer} tileLayer
   * @prop {Object} settings.bing_settings - Bing specific settings.
   */
  function GeolocationBingMap(mapSettings) {
    var bingPromise = new Promise(function (resolve, reject) {
      if (typeof L === 'undefined') {
        setTimeout(function () {
          if (typeof L === 'undefined') {
            reject();
          }
          else {
            resolve();
          }
        }, 1000);
      }
      else {
        resolve();
      }
    });

    this.type = 'bing';

    Drupal.geolocation.GeolocationMapBase.call(this, mapSettings);

    /**
     *
     * @type {MapOptions}
     */
    var defaultBingSettings = {
      zoom: 10
    };

    // Add any missing settings.
    this.settings.bing_settings = $.extend(defaultBingSettings, this.settings.bing_settings);

    // Set the container size.
    this.container.css({
      height: this.settings.bing_settings.height,
      width: this.settings.bing_settings.width
    });

    var that = this;

    bingPromise.then(function () {
      /** @type {Map} */
      var bingMap = L.map(that.container.get(0), {
        center: [that.lat, that.lng],
        zoom: that.settings.bing_settings.zoom,
        zoomControl: false
      });

      var markerLayer = L.layerGroup().addTo(bingMap);

      // Set the tile layer.
      var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(bingMap);

      that.bingMap = bingMap;
      that.markerLayer = markerLayer;
      that.tileLayer = tileLayer;

      that.addPopulatedCallback(function (map) {
        var singleClick;
        map.bingMap.on('click', /** @param {BingMouseEvent} e */ function (e) {
          singleClick = setTimeout(function () {
            map.clickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
          }, 500);
        });

        map.bingMap.on('dblclick', /** @param {BingMouseEvent} e */ function (e) {
          clearTimeout(singleClick);
          map.doubleClickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
        });

        map.bingMap.on('contextmenu', /** @param {BingMouseEvent} e */ function (e) {
          map.contextClickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
        });
      });

      that.initializedCallback();
      that.populatedCallback();
    })
    .catch(function (error) {
      console.error('Bing library not loaded. Bailing out. Error:'); // eslint-disable-line no-console.
      console.error(error);
    });
  }

  GeolocationBingMap.prototype = Object.create(Drupal.geolocation.GeolocationMapBase.prototype);
  GeolocationBingMap.prototype.constructor = GeolocationBingMap;

  Drupal.geolocation.GeolocationBingMap = GeolocationBingMap;
  Drupal.geolocation.addMapProvider('bing', 'GeolocationBingMap');

})(jQuery, Drupal);
