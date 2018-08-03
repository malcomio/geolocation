(function ($, Drupal) {

  'use strict';

  Drupal.geolocation = Drupal.geolocation || {};
  Drupal.geolocation.mapCenter = Drupal.geolocation.mapCenter || {};

  Drupal.geolocation.mapCenter.client_location = function(map, optionId, settings) {
    if (navigator.geolocation) {
      var successCallback = function (position) {
        map.setCenterByCoordinates({lat: position.coords.latitude, lng: position.coords.longitude}, position.coords.accuracy, 'initial_client_location');
        return false;
      };
      navigator.geolocation.getCurrentPosition(successCallback);
    }

    return true;
  }

})(jQuery, Drupal);
