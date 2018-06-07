/**
 * @file
 * Javascript for the Geolocation map widget.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Generic widget behavior.
   *
   * @type {Drupal~behavior}
   * @type {Object} drupalSettings.geolocation
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches Geolocation widget functionality to relevant elements.
   */
  Drupal.behaviors.geolocationWidget = {
    attach: function (context, drupalSettings) {
      $('.geolocation-map-widget', context).once('geolocation-widget-processed').each(function (index, item) {

        var widgetSettings = {};
        var widgetWrapper = $(item);
        widgetSettings.wrapper = widgetWrapper;
        widgetSettings.id = widgetWrapper.attr('id').toString();
        widgetSettings.type = widgetWrapper.data('widget-type').toString();

        if (widgetWrapper.length === 0) {
          return;
        }

        widgetSettings.map = Drupal.geolocation.getMapById(widgetSettings.id + '-map');

        if (!widgetSettings.map) {
          console.error(widgetSettings, 'Could not find widget map.'); // eslint-disable-line no-console
          return;
        }

        if (typeof drupalSettings.geolocation.widgetSettings[widgetSettings.id] !== 'undefined') {
          /** @type {GeolocationMapWidgetSettings} widgetSettings */
          widgetSettings = $.extend(drupalSettings.geolocation.widgetSettings[widgetSettings.id], widgetSettings);
        }

        var widget = Drupal.geolocation.widget.Factory(widgetSettings);

        if (!widget) {
          return;
        }

        var table = $('table.field-multiple-table', widgetWrapper);

        if (table.length) {
          var tableDrag = Drupal.tableDrag[table.attr('id')];

          if (tableDrag) {
            tableDrag.row.prototype.onSwap = function () {
              widget.map.removeMapMarkers();
              widget.loadMarkersFromInput();
            };
          }
        }

        widget.getAllInputs().each(function(index, inputElement) {
          var input = $(inputElement);
          var delta = widget.getAllInputs().index(input);
          var longitude = input.find('input.geolocation-map-input-longitude');
          var latitude = input.find('input.geolocation-map-input-latitude');

          longitude.change(function() {
            var currentValue = $(this).val();
            if (currentValue === '') {
              widget.removeMarker(delta);
            }
            else if (latitude.val() !== '') {
              var location = {lat: Number(latitude.val()), lng: Number(currentValue)};
              var marker = widget.getMarkerByDelta(delta);
              if (marker) {
                widget.updateMarker(location, delta);
              }
              else {
                widget.addMarker(location, delta);
              }
              widget.map.fitMapToMarkers();
            }
          });

          latitude.change(function() {
            var currentValue = $(this).val();
            if (currentValue === '') {
              widget.removeMarker(delta);
            }
            else if (longitude.val() !== '') {
              var location = {lat: Number(currentValue), lng: Number(longitude.val())};
              var marker = widget.getMarkerByDelta(delta);
              if (marker) {
                widget.updateMarker(location, delta);
              }
              else {
                widget.addMarker(location, delta);
              }
              widget.map.fitMapToMarkers();
            }
          });
        });

        widget.map.addInitializedCallback(function (map) {
          widget.loadMarkersFromInput();
          map.fitMapToMarkers();
        });

        // Add the click responders for setting the value.
        var singleClick;

        widget.map.addClickCallback(function (location) {

          // Create 500ms timeout to wait for double click.
          singleClick = setTimeout(function () {
            if (widgetSettings.cardinality === 1) {
              widget.updateInput(location, 0);
              widget.updateMarker(location, 0);
              widget.locationAddedCallback(location);
            }
            else {
              var delta = widget.getNextDelta();
              if (
                  typeof delta === 'undefined'
                  || delta === false
              ) {
                alert(Drupal.t('Maximum number of entries reached.'));
                throw Error('Maximum number of entries reached.');
              }
              widget.addInput(location);
              widget.addMarker(location, delta);
              widget.locationAddedCallback(location);
            }
          }, 500);

        });

        widget.map.addDoubleClickCallback(function () {
          clearTimeout(singleClick);
        });
      });
    }
  };

})(jQuery, Drupal);
