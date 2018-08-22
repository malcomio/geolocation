/**
 * @file
 *   Javascript for the Google Geocoding API geocoder.
 */

/**
 * @property {String[]} drupalSettings.geolocation.geocoder.photon.inputIds
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

            $.getJSON(
                'https://photon.komoot.de/api/',
                {
                  q: request.term,
                  limit: 3,
                  lang: $('html').attr('lang')
                },
                function (data) {
                  if (typeof data.features === 'undefined') {
                    response();
                    return;
                  }
                  $.each(data.features, function (index, result) {
                    autocompleteResults.push({
                      value: result.properties.name,
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
