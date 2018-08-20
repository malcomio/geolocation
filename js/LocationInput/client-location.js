/**
 * @file
 * Javascript for the Geolocation location input.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Generic behavior.
   *
   * @type {Drupal~behavior}
   * @type {Object} drupalSettings.geolocation
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches functionality to relevant elements.
   */
  Drupal.behaviors.locationInputClientLocation = {
    attach: function (context, drupalSettings) {
      $.each(drupalSettings.geolocation.locationInput.clientLocation, function(index, identifier) {
        if (navigator.geolocation) {
          var successCallback = function (position) {
            map.setCenterByCoordinates({lat: position.coords.latitude, lng: position.coords.longitude}, position.coords.accuracy, 'location_input_client_location');
            return false;
          };
          navigator.geolocation.getCurrentPosition(successCallback);
        }
      });
    }
  };

})(jQuery, Drupal);
