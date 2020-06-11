/**
 * @file
 * Javascript for Bing maps integration.
 */

(function ($, Drupal) {
  'use strict';

  // TODO: cross-browser testing.
  // TODO: infobox.

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

    // Ensure that the Bing maps script has loaded before we try to do anything else.
    const bingPromise = new Promise(function (resolve, reject) {
      if (typeof Microsoft === 'undefined' || typeof Microsoft.Maps === 'undefined') {
        setTimeout(function () {
          if (typeof Microsoft === 'undefined' || typeof Microsoft.Maps === 'undefined') {
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
        // const mapCenter = new Microsoft.Maps.Location(map.lat, map.lng);

        let bingMap = new Microsoft.Maps.Map(map.container[0], {
          credentials: bingSettings.api_key,
          // center: mapCenter,
          showDashboard: false,
          showScalebar: true,
          allowHidingLabelsOfRoad: false,
          showLocateMeButton: false,
          showCopyright: false,
          showLogo: false,
          zoom: bingSettings.zoom
        });
        // Set the info popup text.
        let currentInfoWindow = new Microsoft.Maps.Infobox(map.getCenter(), {
          visible: false
        });

        currentInfoWindow.setMap(bingMap);

        Drupal.geolocation.currentInfoWindow = currentInfoWindow;

        that.bingMap = bingMap;
      });

      that.addPopulatedCallback(function (map) {
        // TODO: ???
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

  GeolocationBingMap.prototype.getZoom = function () {
    var that = this;


    return new Promise(function (resolve, reject) {
      resolve(that.bingMap.getZoom());
    });
  };

  GeolocationBingMap.prototype.setZoom = function (zoom, defer) {
    if (typeof zoom === 'undefined') {
      zoom = this.settings.bing_settings.zoom;
    }
    zoom = parseInt(zoom);
    console.log(this.bingMap);

    // let that = this;
    // return new Promise(function (resolve, reject) {
    //   resolve(that.bingMap.setView({
    //     zoom: zoom
    //   }));
    // });


  };


  GeolocationBingMap.prototype.setCenterByCoordinates = function (coordinates, accuracy, identifier) {
    Drupal.geolocation.GeolocationMapBase.prototype.setCenterByCoordinates.call(this, coordinates, accuracy, identifier);
    console.log(this);
    if (typeof accuracy === 'undefined') {
      // TODO: bingMap is undefined.

      // this.bingMap.setView({
      //   center: new Microsoft.Maps.Location(coordinates.lat, coordinates.lng)
      // });
      return;
    }
  };

  Drupal.geolocation.GeolocationBingMap = GeolocationBingMap;
  Drupal.geolocation.addMapProvider('bing', 'GeolocationBingMap');

})(jQuery, Drupal);
