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
 */

/**
 * @name CommonMapSettings
 * @property {Object} settings
 * @property {CommonMapUpdateSettings} dynamic_map
 * @property {String} client_location.enable
 * @property {String} client_location.update_map
 * @property {Boolean} markerScrollToResult
 */

/**
 * @property {CommonMapSettings[]} drupalSettings.geolocation.commonMap
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
    /**
     * @param {GeolocationSettings} drupalSettings.geolocation
     */
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

          if (!map) {
            return;
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

            map.addPopulatedCallback(function (map) {
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
    },
    detach: function (context, drupalSettings) {}
  };

  Drupal.geolocation.commonMap = Drupal.geolocation.commonMap || {};

  Drupal.geolocation.commonMap.dynamicMapViewsAjaxSettings = function(commonMapSettings) {
    // Make sure to load current form DOM element, which will change after every AJAX operation.
    var view = $('.view-id-' + commonMapSettings.dynamic_map.update_view_id + '.view-display-id-' + commonMapSettings.dynamic_map.update_view_display_id);

    if (typeof commonMapSettings.dynamic_map.boundary_filter === 'undefined') {
      return;
    }

    // Extract the view DOM ID from the view classes.
    var matches = /(js-view-dom-id-\w+)/.exec(view.attr('class').toString());
    var currentViewId = matches[1].replace('js-view-dom-id-', 'views_dom_id:');

    var viewInstance = Drupal.views.instances[currentViewId];
    var ajaxSettings = $.extend(true, {}, viewInstance.element_settings);
    ajaxSettings.progress.type = 'none';

    var exposedForm = $('form#views-exposed-form-' + commonMapSettings.dynamic_map.update_view_id.replace(/_/g, '-') + '-' + commonMapSettings.dynamic_map.update_view_display_id.replace(/_/g, '-'));
    if (exposedForm.length) {
      // Add form values.
      jQuery.each( exposedForm.serializeArray(), function( index, field ) {
        var add = {};
        add[field.name] = field.value;
        ajaxSettings.submit = $.extend(ajaxSettings.submit, add);
      });
    }

    // Trigger geolocation bounds specific behavior.
    ajaxSettings.submit = $.extend(ajaxSettings.submit, ['geolocation_common_map_bounds_changed']);

    return ajaxSettings;
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
  Drupal.AjaxCommands.prototype.geolocationCommonMapUpdate = function (ajax, response, status) {
    var $wrapper = response.selector ? $(response.selector) : $(ajax.wrapper);

    // Retain existing map if possible, to avoid jumping and improve UX.
    if (
      $(response.data).find('.geolocation-map-container').length > 0
      && $wrapper.find('.geolocation-map-container').length > 0
    ) {
      var settings = response.settings || ajax.settings || drupalSettings;
      var $existingMapContainer = $wrapper.find('.geolocation-map-container').first().detach();

      var $newContent = $('<div></div>').html(response.data);
      $newContent.find('.geolocation-map-container').remove();

      Drupal.detachBehaviors($wrapper[0], settings);
      $wrapper.replaceWith($newContent);

      $newContent.find('.geolocation-map-wrapper').first().prepend($existingMapContainer);
      Drupal.attachBehaviors($newContent[0], settings);
    }
    else {
      Drupal.AjaxCommands.prototype.insert.call(this, ajax, response, status);
    }
  };

})(jQuery, window, Drupal, drupalSettings);
