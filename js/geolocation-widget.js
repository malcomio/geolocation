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

        widget.map.addPopulatedCallback(function (map) {
          /**
           * @var {GeolocationMapMarker} marker
           */
          $.each(map.mapMarkers, function (index, marker) {
            widget.initializeMarker(marker, index);
          });
        });

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

        widget.map.addPopulatedCallback(function (map) {
          widget.loadMarkersFromInput();
          widget.map.fitMapToMarkers();

          if (
            widgetSettings.autoClientLocationMarker
            && navigator.geolocation && window.location.protocol === 'https:'
          ) {
            navigator.geolocation.getCurrentPosition(function (currentPosition) {
              widget.addMarker({
                lat: currentPosition.coords.latitude,
                lng: currentPosition.coords.longitude
              });
            });
          }
        });

        widget.getAllInputs().each(function(index, inputElement) {
          var input = $(inputElement);
          var delta = widget.getAllInputs().index(input);
          var longitude = input.find('input.geolocation-input-longitude');
          var latitude = input.find('input.geolocation-input-latitude');

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

        widget.map.addClickCallback(function (location) {
          var delta = widget.addInput(location);
          if (delta === false) {
            return;
          }
          widget.addMarker(location, delta);
          widget.locationAddedCallback(location);
          widget.map.fitMapToMarkers();
        });
      });
    }
  };

})(jQuery, Drupal);
