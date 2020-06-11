/**
 * @file
 * Common map handling.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Dynamic map handling.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationCommonMapBing = {
    /**
     * @param {GeolocationSettings} drupalSettings.geolocation
     */
    attach: function (context, drupalSettings) {

      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {CommonMapSettings} commonMapSettings - settings for current
         *     map
         */
        function (mapId, commonMapSettings) {

          if (
            typeof commonMapSettings.dynamic_map !== 'undefined'
            && commonMapSettings.dynamic_map.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            console.log(map);
            if (!map) {
              return;
            }

            /**
             * Update the view depending on dynamic map settings and
             * capability.
             *
             * One of several states might occur now. Possible state depends on
             * whether:
             * - view using AJAX is enabled
             * - map view is the containing (page) view or an attachment
             * - the exposed form is present and contains the boundary filter
             * - map settings are consistent
             *
             * Given these factors, map boundary changes can be handled in one
             * of three ways:
             * - trigger the views AJAX "RefreshView" command
             * - trigger the exposed form causing a regular POST reload
             * - fully reload the website
             *
             * These possibilities are ordered by UX preference.
             */
            if (
              map.container.length
              && map.type === 'bing_maps'
              && !map.container.hasClass('geolocation-common-map-bing-processed')
            ) {
              map.container.addClass('geolocation-common-map-bing-processed');

              map.addPopulatedCallback(function (map) {
                console.log('populatedCallback');
                console.log(map);
              });
            }
          }
        });
    }
  };

})(jQuery, Drupal);
