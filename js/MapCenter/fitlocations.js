(function ($, Drupal) {

  'use strict';

  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.geolocation.mapCenter = Drupal.geolocation.mapCenter || {};

  /**
   * @param settings.reset_zoom {Boolean}
   */
  Drupal.geolocation.mapCenter.fit_bounds = function(map, optionId, settings) {
    map.fitMapToMarkers();

    if (settings.reset_zoom) {
      map.setZoom();
    }

    return false;
  }

})(jQuery, Drupal);
