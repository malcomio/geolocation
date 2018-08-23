/**
 * @file
 *   Javascript for the Google Geocoding API geocoder.
 */

/**
 * @typedef {Object} PhotonResult
 * @property {Object} properties
 * @property {String} properties.street
 * @property {String} properties.city
 * @property {String} properties.state
 * @property {String} properties.postcode
 * @property {String} properties.country
 */

/**
 * @property {String[]} drupalSettings.geolocation.geocoder.photon.inputIds
 * @property {String} drupalSettings.geolocation.geocoder.photon.locationPriority
 * @property {float} drupalSettings.geolocation.geocoder.photon.locationPriority.lat
 * @property {float} drupalSettings.geolocation.geocoder.photon.locationPriority.lon
 */

(function ($, Drupal) {
  'use strict';

  if (typeof Drupal.geolocation.geocoder === 'undefined') {
    return false;
  }

  drupalSettings.geolocation.geocoder.photon = drupalSettings.geolocation.geocoder.photon || {};

  /**
   * Attach geocoder input for Photon
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches views geocoder input for Photon to relevant elements.
   */
  Drupal.behaviors.geolocationGeocoderPhoton = {
    attach: function (context) {
      $.each(drupalSettings.geolocation.geocoder.photon.inputIds, function(index, inputId) {
        var geocoderInput = $('input.geolocation-geocoder-address[data-source-identifier="' + inputId + '"]', context);

        if (geocoderInput.length === 0) {
          return;
        }

        if (geocoderInput.hasClass('geocoder-attached')) {
          return;
        }
        else {
          geocoderInput.addClass('geocoder-attached');
        }

        geocoderInput.autocomplete({
          autoFocus: true,
          source: function (request, response) {
            var autocompleteResults = [];

            var options = {
              q: request.term,
              limit: 3
            };

            var lang = $('html').attr('lang');
            if ($.inArray(lang, ['de', 'en', 'it', 'fr']) !== -1) {
              options.lang = lang;
            }

            if (typeof drupalSettings.geolocation.geocoder.photon.locationPriority !== 'undefined') {
              $.extend(options, drupalSettings.geolocation.geocoder.photon.locationPriority);
            }

            $.getJSON(
                'https://photon.komoot.de/api/',
                options,
                function (data) {
                  if (typeof data.features === 'undefined') {
                    response();
                    return;
                  }
                  /**
                   * @param {int} index
                   * @param {PhotonResult} result
                   */
                  $.each(data.features, function (index, result) {
                    var formatted_address = [];
                    if (typeof result.properties.street !== 'undefined') {
                      formatted_address.push(result.properties.street);
                    }
                    if (typeof result.properties.city !== 'undefined') {
                      formatted_address.push(result.properties.city);
                    }
                    if (typeof result.properties.state !== 'undefined') {
                      formatted_address.push(result.properties.state);
                    }
                    if (typeof result.properties.postcode !== 'undefined') {
                      formatted_address.push(result.properties.postcode);
                    }
                    if (typeof result.properties.country !== 'undefined') {
                      formatted_address.push(result.properties.country);
                    }
                    autocompleteResults.push({
                      value: result.properties.name + ' - ' + formatted_address.join(', '),
                      result: result
                    });
                  });
                  response(autocompleteResults);
                }
            );
          },

          /**
           * Option form autocomplete selected.
           *
           * @param {Object} event - See jquery doc
           * @param {Object} ui - See jquery doc
           * @param {Object} ui.item - See jquery doc
           */
          select: function (event, ui) {
            Drupal.geolocation.geocoder.resultCallback({
                geometry: {
                  location: {
                    lat: function() {
                      return ui.item.result.geometry.coordinates[1];
                    },
                    lng: function() {
                      return ui.item.result.geometry.coordinates[0];
                    }
                  },
                  bounds: ui.item.result.properties.extend
                }
            }, $(event.target).data('source-identifier').toString());
            $('.geolocation-geocoder-state[data-source-identifier="' + $(event.target).data('source-identifier') + '"]').val(1);
          }
        })
        .on('input', function () {
          $('.geolocation-geocoder-state[data-source-identifier="' + $(this).data('source-identifier') + '"]').val(0);
          Drupal.geolocation.geocoder.clearCallback($(this).data('source-identifier').toString());
        });

      });
    },
    detach: function() {}
  };

})(jQuery, Drupal);
