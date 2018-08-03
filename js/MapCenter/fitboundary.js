(function ($, Drupal) {

  'use strict';

  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.geolocation.mapCenter = Drupal.geolocation.mapCenter || {};

  /**
   * @param {float} settings.latNorthEast
   * @param {float} settings.lngNorthEast
   * @param {float} settings.latSouthWest
   * @param {float} settings.lngSouthWest
   */
  Drupal.geolocation.mapCenter.fitboundary = function(map, optionId, settings) {
    var centerBounds = {
      north: settings.latNorthEast,
      east: settings.lngNorthEast,
      south: settings.latSouthWest,
      west: settings.lngSouthWest
    };

    map.fitBoundaries(centerBounds);

    return false;
  }

})(jQuery, Drupal);
