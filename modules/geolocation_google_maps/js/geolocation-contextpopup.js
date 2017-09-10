/**
 * Context popup handling.
 *
 *  * @property {String} contextPopupContent.enable
 * @property {String} contextPopupContent.content
 */
if (
  typeof commonMapSettings.contextPopupContent !== 'undefined'
  && commonMapSettings.contextPopupContent.enable
  && map.type === 'google'
) {

  /** @type {jQuery} */
  var contextContainer = jQuery('<div class="geolocation-context-popup"></div>');
  contextContainer.hide();
  contextContainer.appendTo(map.container);

  /**
   * Context popup handling.
   *
   * @param {GoogleMapLatLng} latLng - Coordinates.
   * @return {GoogleMapPoint} - Pixel offset against top left corner of map container.
   */
  map.googleMap.fromLatLngToPixel = function (latLng) {
    var numTiles = 1 << map.googleMap.getZoom();
    var projection = map.googleMap.getProjection();
    var worldCoordinate = projection.fromLatLngToPoint(latLng);
    var pixelCoordinate = new google.maps.Point(
      worldCoordinate.x * numTiles,
      worldCoordinate.y * numTiles);

    var topLeft = new google.maps.LatLng(
      map.googleMap.getBounds().getNorthEast().lat(),
      map.googleMap.getBounds().getSouthWest().lng()
    );

    var topLeftWorldCoordinate = projection.fromLatLngToPoint(topLeft);
    var topLeftPixelCoordinate = new google.maps.Point(
      topLeftWorldCoordinate.x * numTiles,
      topLeftWorldCoordinate.y * numTiles);

    return new google.maps.Point(
      pixelCoordinate.x - topLeftPixelCoordinate.x,
      pixelCoordinate.y - topLeftPixelCoordinate.y
    );
  };

  google.maps.event.addListener(map.googleMap, 'rightclick', function (event) {
    var content = Drupal.formatString(commonMapSettings.contextPopupContent.content, {
      '@lat': event.latLng.lat(),
      '@lng': event.latLng.lng()
    });

    contextContainer.html(content);

    if (content.length > 0) {
      var pos = map.googleMap.fromLatLngToPixel(event.latLng);
      contextContainer.show();
      contextContainer.css('left', pos.x);
      contextContainer.css('top', pos.y);
    }
  });

  google.maps.event.addListener(map.googleMap, 'click', function (event) {
    if (typeof contextContainer !== 'undefined') {
      contextContainer.hide();
    }
  });
}