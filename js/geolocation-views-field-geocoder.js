/**
 * @file
 *   Javascript for the Google geocoder function, specifically the views filter.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Attach common map style functionality.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches views geolocation field geocoder.
   */
  Drupal.behaviors.geolocationViewsFieldGeocoder = {
    attach: function (context) {

      var proximity_lat = $("input[name='proximity_lat']").once('geolocation-views-field-geocoder-processed');
      var proximity_lng = $("input[name='proximity_lng']").once('geolocation-views-field-geocoder-processed');

      if (
        proximity_lat.length === 0
        || proximity_lng.length === 0
      ) {
        return;
      }

      /**
       * @param {GoogleAddress} address - Google address object.
       */
      Drupal.geolocation.geocoder.addResultCallback(function (address) {
        if (typeof address.geometry.location === 'undefined') {
          return false;
        }

        $(context).find("input[name='proximity_lat']").val(address.geometry.location.lat());
        $(context).find("input[name='proximity_lng']").val(address.geometry.location.lng());
      }, 'views_field_geocoder');

      Drupal.geolocation.geocoder.addClearCallback(function () {
        $(context).find("input[name='proximity_lat']").val('');
        $(context).find("input[name='proximity_lng']").val('');
      }, 'views_field_geocoder');
    }
  };

})(jQuery, Drupal);
