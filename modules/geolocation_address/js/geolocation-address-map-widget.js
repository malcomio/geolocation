/**
 * @file
 * Javascript for the Geolocation address integration.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Generic address integration behavior.
   *
   * @type {Drupal~behavior}
   * @type {Object} drupalSettings.geolocation.address
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches Geolocation widget functionality to relevant elements.
   */
  Drupal.behaviors.geolocationWidget = {
    attach: function (context, drupalSettings) {
      $('.geolocation-map-widget', context).once('geolocation-widget-processed').each(function (index, item) {

        Drupal.t()


        if (drupalSettings.geolocation.widgetSettings[map.id].addressFieldExpliciteActions) {
          if (map.controls.children('button.address-button-locate').length) {
            google.maps.event.addDomListener(map.controls.children('button.address-button-locate')[0], 'click', function (e) {
              // Stop all that bubbling and form submitting.
              e.preventDefault();

              var targetField = drupalSettings.geolocation.widgetSettings[map.id].addressFieldTarget;
              var addressField = $('.field--type-address.field--widget-address-default.field--name-' + targetField.replace(/_/g, '-'));
              if (addressField.length < 1) {
                return;
              }
              var addressDetails = addressField.find('.details-wrapper').first();
              if (addressDetails.length < 1) {
                return;
              }

              var addressData = {};

              addressData.organization = addressDetails.find('.organization').val();
              addressData.addressLine1 = addressDetails.find('.address-line1').val();
              addressData.addressLine2 = addressDetails.find('.address-line2').val();
              addressData.locality = addressDetails.find('.locality').val();
              addressData.administrativeArea = addressDetails.find('.administrative-area').val();
              addressData.postalCode = addressDetails.find('.postal-code').val();

              var search = {};
              search.address = '';
              search.componentRestrictions = {};

              $.each(addressData, function (componentId, componentValue) {
                if (componentValue) {
                  search.address += componentValue + ', ';
                }
              });

              if (addressField.find('.country.form-select').length) {
                search.componentRestrictions.country = addressField.find('.country.form-select').val();
              }

              Drupal.geolocation.geocoderWidget.geocoder.geocode(
                search,

                /**
                 * Google Geocoding API geocode.
                 *
                 * @param {GoogleAddress[]} results - Returned results
                 * @param {String} status - Whether geocoding was successful
                 */
                function (results, status) {
                  if (status === google.maps.GeocoderStatus.OK) {
                    map.googleMap.fitBounds(results[0].geometry.viewport);
                    Drupal.geolocation.geocoderWidget.locationCallback(results[0].geometry.location, mapId);
                  }
                }
              );
            });
          }

          if (map.controls.children('button.address-button-push').length) {
            google.maps.event.addDomListener(map.controls.children('button.address-button-push')[0], 'click', function (e) {
              // Stop all that bubbling and form submitting.
              e.preventDefault();
              Drupal.geolocation.geocoderWidget.setHiddenAddressFieldByReverseLocation(map.googleMap.getCenter(), map);
            });
          }
        }





      });
    }
  };

})(jQuery, Drupal);
