/**
 * @file
 * Javascript for the geolocation geometry google maps widget.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Google maps GeoJSON widget.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Widget.
   */
  Drupal.behaviors.geolocationGeometryWidgetGoogleMaps = {
    attach: function (context) {
      $('.geolocation-geometry-widget-google-maps-geojson', context).once('geolocation-geometry-processed').each(function (index, item) {

        var mapWrapper = $('.geolocation-geometry-widget-google-maps-geojson-map', item);
        var inputWrapper = $('.geolocation-geometry-widget-google-maps-geojson-input', item);

        var mapObject = Drupal.geolocation.getMapById(mapWrapper.attr('id').toString());

        mapObject.addPopulatedCallback(function (mapContainer) {
          /**  @type {google.maps.Map} */
          var map = mapContainer.googleMap;

          var availableControls = ['Point', 'LineString', 'Polygon'];

          map.data.setControls(availableControls);
          map.data.setControlPosition(google.maps.ControlPosition.TOP_CENTER);
          map.data.setStyle({
            editable: true,
            draggable: true
          });

          console.log(JSON.parse(inputWrapper.val().toString()), "input");

          if (inputWrapper.val()) {
            try {
              var geometry = JSON.parse(inputWrapper.val().toString());
              map.data.addGeoJson({
                "type": "FeatureCollection",
                "features": [
                  {
                    "type": "Feature",
                    "id": 'value',
                    "geometry": geometry
                  }
                ]
              });
            }
            catch (error) {
              console.error(error.message);
              return;
            }

            var bounds = new google.maps.LatLngBounds();
            map.data.forEach(function (feature) {
              processPoints(feature.getGeometry(), bounds.extend, bounds);
            });
            mapContainer.fitBoundaries(bounds, 'geolocation_geometry_widget_google_maps');
          }

          function refreshGeoJsonFromData() {
            map.data.toGeoJson(function (geoJson) {
              inputWrapper.val(JSON.stringify(geoJson, null, 2));
            });
          }

          function refreshDataFromGeoJson() {
            var newData = new google.maps.Data({
              map: map,
              style: map.data.getStyle(),
              controls: availableControls
            });
            try {
              var userObject = JSON.parse(inputWrapper.val().toString());
              newData.addGeoJson(userObject);
            }
            catch (error) {
              newData.setMap(null);
              return;
            }
            // No error means GeoJSON was valid!
            map.data.setMap(null);
            map.data = newData;
            bindDataLayerListeners(newData);
          }

          function bindDataLayerListeners(dataLayer) {
            dataLayer.addListener('addfeature', refreshGeoJsonFromData);
            dataLayer.addListener('removefeature', refreshGeoJsonFromData);
            dataLayer.addListener('setgeometry', refreshGeoJsonFromData);

            map.data.addListener('click', function (event) {
              var newPolyPoints = [];

              event.feature.getGeometry().forEachLatLng(function (latlng) {
                if (
                  !(
                    latlng.lat() === event.latLng.lat()
                    && latlng.lng() === event.latLng.lng()
                  )
                ) {
                  newPolyPoints.push(latlng);
                }
              });

              if (newPolyPoints.length < 2) {
                dataLayer.remove(event.feature);
              }
              else {
                var newLinearRing = new google.maps.Data.LinearRing(newPolyPoints);
                var newPoly = new google.maps.Data.Polygon([newLinearRing]);
                event.feature.setGeometry(newPoly);
              }
            });
          }

          /**
           * Process each point in a Geometry, regardless of how deep the points may lie.
           *
           * @param {google.maps.Data.Geometry} geometry The structure to process
           * @param {function(google.maps.LatLng)} callback A function to call on each
           *     LatLng point encountered (e.g. Array.push)
           * @param {Object} thisArg The value of 'this' as provided to 'callback' (e.g.
           *     myArray)
           */
          function processPoints(geometry, callback, thisArg) {
            if (geometry instanceof google.maps.LatLng) {
              callback.call(thisArg, geometry);
            }
            else if (geometry instanceof google.maps.Data.Point) {
              callback.call(thisArg, geometry.get());
            }
            else {
              geometry.getArray().forEach(function (g) {
                processPoints(g, callback, thisArg);
              });
            }
          }

          bindDataLayerListeners(map.data);

          google.maps.event.addDomListener(
            inputWrapper,
            'input',
            refreshDataFromGeoJson);
        });
      });
    },
    detach: function () {}
  };

})(jQuery, Drupal);
