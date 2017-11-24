/**
 * @file
 * Handle the common map.
 */

/**
 * @name CommonMapUpdateSettings
 * @property {String} enable
 * @property {String} hide_form
 * @property {number} views_refresh_delay
 * @property {String} update_view_id
 * @property {String} update_view_display_id
 * @property {String} boundary_filter
 * @property {String} parameter_identifier
 * @property {Boolean} enable_refresh_event
 */

/**
 * @name CommonMapSettings
 * @property {Object} settings
 * @property {CommonMapUpdateSettings} dynamic_map
 * @property {String} client_location.enable
 * @property {String} client_location.update_map
 * @property {Boolean} showRawLocations
 * @property {Boolean} markerScrollToResult
 */

/**
 * @property {CommonMapSettings[]} drupalSettings.geolocation.commonMap
 */

/**
 * @property {function(CommonMapUpdateSettings)} GeolocationMapSettings.updateDrupalView
 */

(function ($, window, Drupal, drupalSettings) {
  'use strict';

  /**
   * Attach common map style functionality.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationCommonMap = {
    attach: function (context, drupalSettings) {
      if (typeof drupalSettings.geolocation === 'undefined') {
        return;
      }

      $.each(
        drupalSettings.geolocation.commonMap,

        /**
         * @param {String} mapId - ID of current map
         * @param {CommonMapSettings} commonMapSettings - settings for current map
         */
        function (mapId, commonMapSettings) {

          var map = Drupal.geolocation.getMapById(mapId);

          // Hide the graceful-fallback HTML list; map will propably work now.
          // Map-container is not hidden by default in case of graceful-fallback.
          if (typeof commonMapSettings.showRawLocations !== 'undefined') {
            if (commonMapSettings.showRawLocations) {
              map.addLoadedCallback(function (map) {
                map.wrapper.find('.geolocation-location').show();
              });
            }
          }

          /*
           * Hide form if requested.
           */
          if (
            typeof commonMapSettings.dynamic_map !== 'undefined'
            && commonMapSettings.dynamic_map.enable
            && commonMapSettings.dynamic_map.hide_form
            && typeof commonMapSettings.dynamic_map.parameter_identifier !== 'undefined'
          ) {
            var exposedForm = $('form#views-exposed-form-' + commonMapSettings.dynamic_map.update_view_id.replace(/_/g, '-') + '-' + commonMapSettings.dynamic_map.update_view_display_id.replace(/_/g, '-'));

            if (exposedForm.length === 1) {
              exposedForm.find('input[name^="' + commonMapSettings.dynamic_map.parameter_identifier + '"]').each(function (index, item) {
                $(item).parent().hide();
              });

              // Hide entire form if it's empty now, except form-submit.
              if (exposedForm.find('input:visible:not(.form-submit), select:visible').length === 0) {
                exposedForm.hide();
              }
            }
          }

          if (
            typeof commonMapSettings.markerScrollToResult !== 'undefined'
            && commonMapSettings.markerScrollToResult === true
          ) {
            map.addLoadedCallback(function (map) {
              $.each(map.mapMarkers, function (index, marker) {
                marker.addListener('click', function () {
                  var target = $('[data-location-id="' + location.data('location-id') + '"]:visible').first();

                  // Alternatively select by class.
                  if (target.length === 0) {
                    target = $('.geolocation-location-id-' + location.data('location-id') + ':visible').first();
                  }

                  if (target.length === 1) {
                    $('html, body').animate({
                      scrollTop: target.offset().top
                    }, 'slow');
                  }

                });
              });
            });
          }
        }
      );

    }
  };

  /**
   * Insert updated map contents into the document.
   *
   * ATTENTION: This is a straight ripoff from misc/ajax.js ~line 1017 insert() function.
   * Please read all code commentary there first!
   *
   * @param {Drupal.Ajax} ajax
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {string} response.data
   *   The data to use with the jQuery method.
   * @param {string} [response.method]
   *   The jQuery DOM manipulation method to be used.
   * @param {string} [response.selector]
   *   A optional jQuery selector string.
   * @param {object} [response.settings]
   *   An optional array of settings that will be used.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.geolocationCommonMapsUpdate = function (ajax, response, status) {

    // See function comment for code origin first before any changes!
    var viewWrapper = response.selector ? $(response.selector) : $(ajax.wrapper);
    var settings = response.settings || ajax.settings || drupalSettings;

    var newContent = $('<div></div>').html(response.data).contents();

    if (newContent.length !== 1 || newContent.get(0).nodeType !== 1) {
      newContent = newContent.parent();
    }

    Drupal.detachBehaviors(viewWrapper.get(0), settings);

    var commonMapStyle = false;

    if (
      newContent.find('.geolocation-map-container').length > 0
      && viewWrapper.find('.geolocation-map-container').length > 0
    ) {
      commonMapStyle = true;
    }

    if (commonMapStyle) {
      // Retain existing map if possible, to avoid jumping and improve UX.
      newContent.find('.geolocation-map-container').first().remove();
      var map = viewWrapper.find('.geolocation-map-container').first();
      map.insertBefore(viewWrapper);
    }

    viewWrapper.replaceWith(newContent);

    if (commonMapStyle) {
      map.prependTo(viewWrapper.find('.geolocation-map-wrapper'));
    }

    if (newContent.parents('html').length > 0) {
      Drupal.attachBehaviors(newContent.get(0), settings);
    }
  };

})(jQuery, window, Drupal, drupalSettings);
