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

    const bingPromise = new Promise(function (resolve, reject) {
      console.log('in promise');
      if (typeof Microsoft === 'undefined') {
        setTimeout(function () {
          if (typeof Microsoft === 'undefined') {
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

    let that = this;
    // Ensure that the Bing script has loaded before trying to do anything.
    bingPromise.then(function () {

      that.addInitializedCallback(function (map) {

        const bingSettings = map.settings.bing_settings;

        map.bingMap = new Microsoft.Maps.Map(map.container[0], {
          credentials: bingSettings.api_key,
        });

        const mapCenter = new Microsoft.Maps.Location(map.lat, map.lng);
        map.bingMap.setOptions({
          // center: mapCenter,
          showDashboard: false,
          showScalebar: true,
          disableZooming: true,
          disablePanning: true,
          allowHidingLabelsOfRoad: false,
          showLocateMeButton: false,
          showCopyright: false,
          showLogo: false,
          zoom: bingSettings.zoom,
        });

        console.log(map);
      });

      that.addPopulatedCallback(function (map) {
        // var singleClick;
        // map.bingMap.on('click', /** @param {BingMouseEvent} e */ function (e) {
        //   singleClick = setTimeout(function () {
        //     map.clickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
        //   }, 500);
        // });
        //
        // map.bingMap.on('dblclick', /** @param {BingMouseEvent} e */ function (e) {
        //   clearTimeout(singleClick);
        //   map.doubleClickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
        // });
        //
        // map.bingMap.on('contextmenu', /** @param {BingMouseEvent} e */ function (e) {
        //   map.contextClickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
        // });
      });

      that.initializedCallback();
      that.populatedCallback();

    });
  }

  GeolocationBingMap.prototype = Object.create(Drupal.geolocation.GeolocationMapBase.prototype);
  GeolocationBingMap.prototype.constructor = GeolocationBingMap;

  Drupal.geolocation.GeolocationBingMap = GeolocationBingMap;
  Drupal.geolocation.addMapProvider('bing', 'GeolocationBingMap');

})(jQuery, Drupal);
