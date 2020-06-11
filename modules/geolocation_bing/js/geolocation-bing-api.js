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
        const mapCenter = new Microsoft.Maps.Location(map.lat, map.lng);

        console.log(bingSettings);

        map.bingMap = new Microsoft.Maps.Map(map.container[0], {
          credentials: bingSettings.api_key,
          // center: mapCenter,
          showDashboard: false,
          showScalebar: true,
          disableZooming: true,
          disablePanning: true,
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
          pin.metadata = {
            // TODO: get this dynamically.
            title: 'hello',
            description: 'blah'
          };
          Microsoft.Maps.Events.addHandler(pin, 'click', pushpinClicked);

          // Add the pushpin to the map
          map.bingMap.entities.push(pin);

        }

        function pushpinClicked(e) {
          //Make sure the infobox has metadata to display.
          if (e.target.metadata) {
            //Set the infobox options with the metadata of the pushpin.
            infobox.setOptions({
              location: e.target.getLocation(),
              title: e.target.metadata.title,
              description: e.target.metadata.description,
              visible: true
            });
          }
        }

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
