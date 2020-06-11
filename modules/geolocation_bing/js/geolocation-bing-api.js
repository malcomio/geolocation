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

        map.bingMap = new Microsoft.Maps.Map(map.container[0], {
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

        //Create an infobox at the center of the map but don't show it.
        let infobox = new Microsoft.Maps.Infobox(map.getCenter(), {
          visible: false
        });

        infobox.setMap(map.bingMap);

        // Add the pins from view.
        for (let i = 0; i < map.mapMarkers.length; i++) {
          const thisMarker = map.mapMarkers[i];
          const pinLocation = new Microsoft.Maps.Location(thisMarker.position.lat, thisMarker.position.lng);

          let pin = new Microsoft.Maps.Pushpin(pinLocation);

          // Add the pushpin to the map
          map.bingMap.entities.push(pin);
        }

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
