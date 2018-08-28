/**
 * @file
 *   Javascript for the map geocoder widget.
 */

/**
 * @param {GeolocationMapWidgetInterface[]} Drupal.geolocation.widgets - List of widget instances
 * @param {Object} Drupal.geolocation.widget - Prototype container
 * @param {GeolocationMapWidgetSettings[]} drupalSettings.geolocation.widgetSettings - Additional widget settings
 */

/**
 * @name GeolocationMapWidgetSettings
 * @property {String} autoClientLocationMarker
 * @property {jQuery} wrapper
 * @property {String} id
 * @property {String} type
 * @property {GeolocationMapInterface} map
 * @property {String} fieldName
 * @property {String} cardinality
 */

/**
 * Callback for location found or set by widget.
 *
 * @callback geolocationWidgetLocationCallback
 * @param {GeolocationCoordinates} location - Location.
 * @param {int} [delta] - Delta.
 */

/**
 * Callback for location unset by widget.
 *
 * @callback geolocationGeocoderClearCallback
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationMapWidgetInterface
 * @property {GeolocationMapWidgetSettings} settings
 * @property {String} id
 * @property {jQuery} wrapper
 * @property {jQuery} container
 * @property {Object[]} mapMarkers
 * @property {geolocationWidgetLocationCallback[]} locationAddedCallbacks
 * @property {geolocationWidgetLocationCallback[]} locationModifiedCallbacks
 * @property {geolocationWidgetLocationCallback[]} locationRemovedCallbacks
 * @property {geolocationGeocoderClearCallback[]} clearCallbacks
 *
 * @property {function({GeolocationCoordinates})} locationAddedCallback - Executes all {geolocationWidgetLocationCallback} callbacks.
 * @property {function({geolocationWidgetLocationCallback})} addLocationAddedCallback - Adds a callback that will be called when a location is set.
 *
 * @property {function({GeolocationCoordinates}, {int})} locationModifiedCallback - Executes all {geolocationWidgetLocationCallback} modified callbacks.
 * @property {function({geolocationWidgetLocationCallback})} addLocationModifiedCallback - Adds a callback that will be called when a location is set.
 *
 * @property {function({int})} locationRemovedCallback - Executes all {geolocationWidgetLocationCallback} modified callbacks.
 * @property {function({geolocationWidgetLocationCallback})} addLocationRemovedCallback - Adds a callback that will be called when a location is removed.
 *
 * @property {function():{GeolocationMapMarker[]}} loadMarkersFromInput - Load markers from input and add to map.
 * @property {function({int}):{GeolocationMapMarker}} getMarkerByDelta - Get map marker by delta.
 * @property {function():{int}} getNextDelta - Get next delta.
 * @property {function({int}):{jQuery}} getInputByDelta - Get map input by delta.
 *
 * @property {function({GeolocationMapMarker}, {int}=):{int}} initializeMarker - Initialize markers.
 * @property {function({GeolocationCoordinates}, {int}=):{int}} addInput - Add input.
 * @property {function({GeolocationCoordinates}, {int})} updateInput - Update input.
 * @property {function({int})} removeInput - Remove input.
 * @property {function({GeolocationCoordinates}, {int}=):{int}} addMarker - Add marker.
 * @property {function({GeolocationCoordinates}, {int})} updateMarker - Update marker.
 * @property {function({int})} removeMarker - Remove marker.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */

  Drupal.geolocation.widget = Drupal.geolocation.widget || {};

  Drupal.geolocation.widgets = Drupal.geolocation.widgets || [];

  /**
   * Geolocation map widget.
   *
   * @constructor
   * @abstract
   * @implements {GeolocationMapWidgetInterface}
   * @param {GeolocationMapWidgetSettings} widgetSettings - Setting to create map.
   */
  function GeolocationMapWidgetBase(widgetSettings) {

    this.locationAddedCallbacks = [];
    this.locationModifiedCallbacks = [];
    this.locationRemovedCallbacks = [];

    this.clearCallbacks = [];

    this.settings = widgetSettings || {};
    this.wrapper = widgetSettings.wrapper;
    this.fieldName = widgetSettings.fieldName;
    this.cardinality = widgetSettings.cardinality || 1;

    this.map = widgetSettings.map;
    this.id = widgetSettings.id;

    return this;
  }

  GeolocationMapWidgetBase.prototype = {

    locationAddedCallback: function (location) {
      $.each(this.locationAddedCallbacks, function (index, callback) {
        callback(location);
      });
    },
    addLocationAddedCallback: function (callback) {
      this.locationAddedCallbacks.push(callback);
    },
    locationModifiedCallback: function (location, delta) {
      $.each(this.locationModifiedCallbacks, function (index, callback) {
        callback(location, delta);
      });
    },
    addLocationModifiedCallback: function (callback) {
      this.locationModifiedCallbacks.push(callback);
    },
    locationRemovedCallback: function (delta) {
      $.each(this.locationRemovedCallbacks, function (index, callback) {
        callback(delta);
      });
    },
    addLocationRemovedCallback: function (callback) {
      this.locationRemovedCallbacks.push(callback);
    },
    loadMarkersFromInput: function() {
      var that = this;
      $('.geolocation-widget-input', this.wrapper).each(function(delta, inputElement) {
        var input = $(inputElement);
        var lng = input.find('input.geolocation-input-longitude').val();
        var lat = input.find('input.geolocation-input-latitude').val();

        if (lng && lat) {
          that.addMarker({lat: Number(lat), lng: Number(lng)}, delta);
        }
      });
    },
    getAllInputs: function() {
      return $('.geolocation-widget-input', this.wrapper);
    },
    getMarkerByDelta: function (delta) {
      delta = parseInt(delta) || 0;
      var marker = null;

      $.each(this.map.mapMarkers, function(index, currentMarker) {
        /** @param {GeolocationMapMarker} currentMarker */
        if (currentMarker.delta === delta) {
          marker = currentMarker;
          return false;
        }
      });
      return marker;
    },
    getInputByDelta: function (delta) {
      delta = parseInt(delta) || 0;
      var input = this.getAllInputs().eq(delta);
      if (input.length) {
        return input;
      }
    },
    getNextDelta: function(delta) {
      if (this.cardinality === 1) {
        return 0;
      }
      var that = this;
      var lastDelta = this.getAllInputs().length - 1;

      if (typeof delta === 'undefined') {
        delta = lastDelta;
      }

      var input = this.getInputByDelta(delta);

      // Failsafe.
      if (input === false) {
        return false;
      }
      // Current input already full.
      else if (
          input.find('input.geolocation-input-longitude').val()
          || input.find('input.geolocation-input-latitude').val()
      ) {
        // Check if next input can used and add if required.
        if (
            (delta + 1) < this.cardinality
            || this.cardinality === -1
        ) {
          // Check if new input required.
          if ((delta + 1) > lastDelta) {
            that.addNewEmptyInput();
            alert("Please try again.");
            return false;
          }
          else if ((delta + 1) === lastDelta) {
            setTimeout(function() {
              that.addNewEmptyInput();
            }, 100);
            return delta + 1;
          }
          else {
            return delta + 1;
          }
        }
        // No further inputs available.
        alert(Drupal.t('Maximum number of entries reached.'));
        return false;
      }
      // First input is empty, use it.
      else if (delta === 0) {
        setTimeout(function() {
          that.addNewEmptyInput();
        }, 100);
        return 0;
      }
      else {
        return this.getNextDelta(delta - 1);
      }
    },
    addNewEmptyInput: function () {
      var button = this.wrapper.find('[name="' + this.fieldName + '_add_more"]');
      if (button.length) {
        button.trigger("mousedown");
      }
    },
    addInput: function (location, delta) {
      if (typeof delta === 'undefined') {
        delta = this.getNextDelta();
      }

      if (
        typeof delta === 'undefined'
        || delta === false
      ) {
        return delta;
      }
      var input = this.getInputByDelta(delta);
      if (input) {
        input.find('input.geolocation-input-longitude').val(location.lng);
        input.find('input.geolocation-input-latitude').val(location.lat);
      }

      return delta;
    },
    initializeMarker: function (marker, delta) {
      marker.delta = delta;
    },
    addMarker: function (location, delta) {
      var marker = this.getMarkerByDelta(delta);
      if (
        typeof marker !== 'undefined'
        && typeof marker !== false
      ) {
        if (marker) {
          this.map.removeMapMarker(marker);
        }
      }
    },
    updateInput: function (location, delta) {
      var input = this.getInputByDelta(delta);
      input.find('input.geolocation-input-longitude').val(location.lng);
      input.find('input.geolocation-input-latitude').val(location.lat);
    },
    updateMarker: function (location, delta) {},
    removeMarker: function (delta) {
      var marker = this.getMarkerByDelta(delta);

      if (marker) {
        this.map.removeMapMarker(marker);
      }
    },
    removeInput: function (delta) {
      var input = this.getInputByDelta(delta);
      input.find('input.geolocation-input-longitude').val('');
      input.find('input.geolocation-input-latitude').val('');
    }
  };

  Drupal.geolocation.widget.GeolocationMapWidgetBase = GeolocationMapWidgetBase;

  /**
   * Factory creating map instances.
   *
   * @constructor
   * @param {GeolocationMapWidgetSettings} widgetSettings - The widget settings.
   * @param {Boolean} [reset] Force creation of new widget.
   * @return {GeolocationMapWidgetInterface|boolean} - New or updated widget.
   */
  function Factory(widgetSettings, reset) {
    reset = reset || false;
    widgetSettings.type = widgetSettings.type || 'google';

    var widget = null;

    /**
     * Previously stored map.
     * @type {boolean|GeolocationMapInterface}
     */
    var existingWidget = false;

    $.each(Drupal.geolocation.widgets, function (index, widget) {
      if (widget.id === widgetSettings.id) {
        existingWidget = Drupal.geolocation.widgets[index];
      }
    });

    if (reset === true || !existingWidget) {
      if (typeof Drupal.geolocation.widget[Drupal.geolocation.widget.widgetProviders[widgetSettings.type]] !== 'undefined') {
        var widgetProvider = Drupal.geolocation.widget[Drupal.geolocation.widget.widgetProviders[widgetSettings.type]];
        widget = new widgetProvider(widgetSettings);
        if (widget) {
          Drupal.geolocation.widgets.push(widget);
        }
      }
    }
    else {
      widget = existingWidget;
    }

    if (!widget) {
      console.error(widgetSettings, "Widget could not be initialzed"); // eslint-disable-line no-console
      return false;
    }

    return widget;
  }

  Drupal.geolocation.widget.Factory = Factory;

  /**
   * @type {Object[]}
   */
  Drupal.geolocation.widget.widgetProviders = {};

  Drupal.geolocation.widget.addWidgetProvider = function (type, name) {
    Drupal.geolocation.widget.widgetProviders[type] = name;
  };

  /**
   * Get widget by ID.
   *
   * @param {String} id - Widget ID to retrieve.
   * @return {GeolocationMapWidgetInterface|boolean} - Retrieved widget or false.
   */
  Drupal.geolocation.widget.getWidgetById = function (id) {
    var widget = false;
    $.each(Drupal.geolocation.widgets, function (index, currentWidget) {
      if (currentWidget.id === id) {
        widget = currentWidget;
      }
    });

    return widget;
  };

})(jQuery, Drupal);
