(function ($, Drupal) {

  'use strict';

  /**
   * Disable POI.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationContextPopup = {
    attach: function (context, drupalSettings) {

      Drupal.geolocation.executeFeatureOnAllMaps(
        'map_disable_poi',
        function (map, featureSettings) {
          map.addInitializedCallback(function (map) {

            var styles = [];
            if (typeof map.googleMap.styles !== 'undefined') {
              styles = map.googleMap.styles;
            }
            styles = $.merge(styles, [{
              featureType: "poi",
              stylers: [
                { visibility: "off" }
              ]
            }]);

            map.googleMap.setOptions({styles: styles});
          });

          return true;
        },
        drupalSettings
      );
    },
    detach: function (context, drupalSettings) {}
  };
})(jQuery, Drupal);
