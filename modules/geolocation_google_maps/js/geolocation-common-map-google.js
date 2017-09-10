/**
 * Dynamic map handling aka "AirBnB mode".
 */
if (
  typeof commonMapSettings.dynamic_map !== 'undefined'
  && commonMapSettings.dynamic_map.enable
) {

  /**
   * Update the view depending on dynamic map settings and capability.
   *
   * One of several states might occur now. Possible state depends on whether:
   * - view using AJAX is enabled
   * - map view is the containing (page) view or an attachment
   * - the exposed form is present and contains the boundary filter
   * - map settings are consistent
   *
   * Given these factors, map boundary changes can be handled in one of three ways:
   * - trigger the views AJAX "RefreshView" command
   * - trigger the exposed form causing a regular POST reload
   * - fully reload the website
   *
   * These possibilities are ordered by UX preference.
   *
   * @param {CommonMapUpdateSettings} dynamic_map_settings
   *   The dynamic map settings to update the map.
   */
  if (typeof map.updateDrupalView === 'undefined') {
    map.updateDrupalView = function (dynamic_map_settings) {
      // Make sure to load current form DOM element, which will change after every AJAX operation.
      var exposedForm = $('form#views-exposed-form-' + dynamic_map_settings.update_view_id.replace(/_/g, '-') + '-' + dynamic_map_settings.update_view_display_id.replace(/_/g, '-'));

      var currentBounds = map.googleMap.getBounds();
      var update_path = '';

      if (
        typeof dynamic_map_settings.boundary_filter !== 'undefined'
      ) {
        if (exposedForm.length) {
          exposedForm.find('input[name="' + dynamic_map_settings.parameter_identifier + '[lat_north_east]"]').val(currentBounds.getNorthEast().lat());
          exposedForm.find('input[name="' + dynamic_map_settings.parameter_identifier + '[lng_north_east]"]').val(currentBounds.getNorthEast().lng());
          exposedForm.find('input[name="' + dynamic_map_settings.parameter_identifier + '[lat_south_west]"]').val(currentBounds.getSouthWest().lat());
          exposedForm.find('input[name="' + dynamic_map_settings.parameter_identifier + '[lng_south_west]"]').val(currentBounds.getSouthWest().lng());

          $('input[type=submit], input[type=image], button[type=submit]', exposedForm).not('[data-drupal-selector=edit-reset]').trigger('click');
        }
        // No AJAX, no form, just enforce a page reload with GET parameters set.
        else {
          if (window.location.search.length) {
            update_path = window.location.search + '&';
          }
          else {
            update_path = '?';
          }
          update_path += dynamic_map_settings.parameter_identifier + '[lat_north_east]=' + currentBounds.getNorthEast().lat();
          update_path += '&' + dynamic_map_settings.parameter_identifier + '[lng_north_east]=' + currentBounds.getNorthEast().lng();
          update_path += '&' + dynamic_map_settings.parameter_identifier + '[lat_south_west]=' + currentBounds.getSouthWest().lat();
          update_path += '&' + dynamic_map_settings.parameter_identifier + '[lng_south_west]=' + currentBounds.getSouthWest().lng();

          window.location = update_path;
        }
      }
    };
  }

  if (typeof commonMapSettings.dynamic_map.enable_refresh_event === 'undefined') {
    commonMapSettings.dynamic_map.enable_refresh_event = false;
  }

  if (map.wrapper.data('geolocationAjaxProcessed') !== 1) {
    map.addLoadedCallback(function (map) {
      var geolocationMapIdleTimer;
      map.googleMap.addListener('bounds_changed', function () {
        if (!commonMapSettings.dynamic_map.enable_refresh_event) {
          return;
        }
        clearTimeout(geolocationMapIdleTimer);
        geolocationMapIdleTimer = setTimeout(function () {
          map.updateDrupalView(commonMapSettings.dynamic_map);
        }, commonMapSettings.dynamic_map.views_refresh_delay);
      });
    });
  }
}