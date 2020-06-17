/**
 * @file
 * Javascript for Bing maps integration.
 */

/**
 * Callback once Bing Maps have loaded asynchronously.
 * @constructor
 */
function GeolocationBingMapLoadedCallback() {

  const maps = Drupal.geolocation.maps;

  for (let i = 0; i < maps.length; i++) {
    const map = maps[i];
    const bingSettings = map.settings.bing_settings;

    let bingMap = new Microsoft.Maps.Map(map.container[0], {
      credentials: bingSettings.api_key,
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

    map.bingMap = bingMap;

    map.initializedCallback();
    map.populatedCallback();
  }
}

(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationBingMap element.
   *
   * @constructor
   * @augments {GeolocationMapBase}
   * @implements {GeolocationMapInterface}
   * @inheritDoc
   */
  function GeolocationBingMap(mapSettings) {

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

    this.addPopulatedCallback(function (map) {
      let locations = [];
      for (let i = 0; i < map.mapMarkers.length; i++) {
        const thisMarker = map.mapMarkers[i];
        const thisLocation = thisMarker.position;
        if (thisLocation.lat && thisLocation.lng) {

          // Generate a location pin.
          const pinLocation = new Microsoft.Maps.Location(thisLocation.lat, thisLocation.lng);
          locations.push(pinLocation);

          // TODO: customizable pins.
          let pin = new Microsoft.Maps.Pushpin(pinLocation);

          // Do we have any info to put into the infobox?
          var content = thisMarker.locationWrapper.find('.location-content');
          if (content.length) {
            content = content.html();

            pin.metadata = {
              description: content.toString()
            };
            Microsoft.Maps.Events.addHandler(pin, 'click', pushpinClicked);
          }

          map.bingMap.entities.push(pin);

          function pushpinClicked(e) {
            // Make sure the infobox has metadata to display.
            if (e.target.metadata) {
              // Set the infobox options with the metadata of the pushpin.
              Drupal.geolocation.currentInfoWindow.setOptions({
                location: e.target.getLocation(),
                title: e.target.metadata.title,
                description: e.target.metadata.description,
                visible: true
              });
            }
          }
        }
      }

      // Center the map based on the locations.
      if (locations.length) {
        var rect = Microsoft.Maps.LocationRect.fromLocations(locations);
        map.bingMap.setView({bounds: rect, padding: 20});
      }

      console.log(locations);

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
    // TODO: does this function even need to exist? If so, what are we doing here?
  };

  GeolocationBingMap.prototype.setCenterByCoordinates = function (coordinates, accuracy, identifier) {
    Drupal.geolocation.GeolocationMapBase.prototype.setCenterByCoordinates.call(this, coordinates, accuracy, identifier);
    if (typeof accuracy === 'undefined') {
      // TODO: does this function even need to exist?
      return;
    }
  };

  Drupal.geolocation.GeolocationBingMap = GeolocationBingMap;
  Drupal.geolocation.addMapProvider('bing', 'GeolocationBingMap');

})(jQuery, Drupal);
