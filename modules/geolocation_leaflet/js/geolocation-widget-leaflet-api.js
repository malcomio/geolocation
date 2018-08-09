/**
 * @file
 *   Javascript for the map geocoder widget.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationLeafletMapWidget element.
   *
   * @constructor
   * @augments {GeolocationMapWidgetBase}
   * @implements {GeolocationMapWidgetInterface}
   * @inheritDoc
   */
  function GeolocationLeafletMapWidget(widgetSettings) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.call(this, widgetSettings);

    return this;
  }
  GeolocationLeafletMapWidget.prototype = Object.create(Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype);
  GeolocationLeafletMapWidget.prototype.constructor = GeolocationLeafletMapWidget;
  GeolocationLeafletMapWidget.prototype.addMarker = function (location, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.addMarker.call(this, location, delta);

    if (typeof delta === 'undefined') {
      delta = this.getNextDelta();
    }

    var marker = this.map.setMapMarker({
      position: location
    });
    marker = this.initializeMarker(marker, delta);

    return marker;
  };
  GeolocationLeafletMapWidget.prototype.initializeMarker = function (marker, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.initializeMarker.call(this, marker, delta);

    var location = marker.getLatLng();
    marker.setPopupContent(Drupal.t('[@delta] Latitude: @latitude Longitude: @longitude', {
      '@delta': delta,
      '@latitude': location.lat,
      '@longitude': location.lng
    }));
    marker.dragging.enable();
    marker.bindTooltip(String((delta + 1)), {
      permanent: true,
      direction: 'top'
    });

    var that = this;
    marker.on('dragend', function(e) {
      var latLng = e.target.getLatLng();
      that.updateInput({lat: latLng.lat, lng: latLng.lng}, marker.delta);
    });

    marker.on('click', function() {
      that.removeInput(marker.delta);
      that.removeMarker(marker.delta);
      that.locationRemovedCallback(marker.delta);
    });

    return marker;
  };
  GeolocationLeafletMapWidget.prototype.updateMarker = function (location, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.updateMarker.call(this, location, delta);

    var marker = this.getMarkerByDelta(delta);
    marker.setLatLng(location);

    return marker;
  };
  Drupal.geolocation.widget.GeolocationLeafletMapWidget = GeolocationLeafletMapWidget;

  Drupal.geolocation.widget.addWidgetProvider('geolocation_leaflet', 'GeolocationLeafletMapWidget');

})(jQuery, Drupal);
