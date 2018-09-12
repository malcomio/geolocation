/**
 * @file
 *   Javascript for widget API.
 */

/**
 * @param {GeolocationWidgetInterface[]} Drupal.geolocation.widgets - List of widget instances
 * @param {Object} Drupal.geolocation.widget - Prototype container
 * @param {GeolocationWidgetSettings[]} drupalSettings.geolocation.widgetSettings - Additional widget settings
 */

/**
 * @name GeolocationWidgetSettings
 * @property {String} autoClientLocationMarker
 * @property {jQuery} wrapper
 * @property {String} id
 * @property {String} type
 * @property {String} fieldName
 * @property {String} cardinality
 */

/**
 * Callback for location found or set by widget.
 *
 * @callback geolocationWidgetLocationCallback
 * @param {String} Identifier
 * @param {GeolocationCoordinates} [location] - Location.
 * @param {int} [delta] - Delta.
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationWidgetInterface
 * @property {GeolocationWidgetSettings} settings
 * @property {String} id
 * @property {jQuery} wrapper
 * @property {jQuery} container
 * @property {geolocationWidgetLocationCallback[]} locationAlteredCallbacks
 *
 * @property {function({String}, {GeolocationCoordinates}|null, {int}|null)} locationAlteredCallback - Executes all {geolocationWidgetLocationCallback} modified callbacks.
 * @property {function({geolocationWidgetLocationCallback})} addLocationAlteredCallback - Adds a callback that will be called when a location is set.
 *
 * @property {function():{int}} getNextDelta - Get next delta.
 * @property {function({int}):{jQuery}} getInputByDelta - Get widget input by delta.
 *
 * @property {function({GeolocationCoordinates}, {int}=):{int}} addInput - Add input.
 * @property {function({GeolocationCoordinates}, {int})} updateInput - Update input.
 * @property {function({int})} removeInput - Remove input.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */

  Drupal.geolocation.widget = Drupal.geolocation.widget || {};

  Drupal.geolocation.widgets = Drupal.geolocation.widgets || [];

  /**
   * Geolocation widget.
   *
   * @constructor
   * @abstract
   * @implements {GeolocationWidgetInterface}
   * @param {GeolocationWidgetSettings} widgetSettings - Setting to create widget.
   */
  function GeolocationMapWidgetBase(widgetSettings) {

    this.locationAlteredCallbacks = [];

    this.settings = widgetSettings || {};
    this.wrapper = widgetSettings.wrapper;
    this.fieldName = widgetSettings.fieldName;
    this.cardinality = widgetSettings.cardinality || 1;

    this.inputChangedEventPaused = false;

    this.id = widgetSettings.id;

    return this;
  }

  GeolocationMapWidgetBase.prototype = {

    locationAlteredCallback: function (identifier, location, delta) {
      if (
          typeof delta === 'undefined'
          || delta === null
      ) {
        delta = this.getNextDelta();
      }
      if (delta === false) {
        return;
      }
      $.each(this.locationAlteredCallbacks, function (index, callback) {
        callback(location, delta, identifier);
      });
    },
    addLocationAlteredCallback: function (callback) {
      this.locationAlteredCallbacks.push(callback);
    },
    getAllInputs: function() {
      return $('.geolocation-widget-input', this.wrapper);
    },
    refreshWidgetByInputs: function() {
      var that = this;
      this.getAllInputs().each(function(delta, inputElement) {
        var input = $(inputElement);
        var lng = input.find('input.geolocation-input-longitude').val();
        var lat = input.find('input.geolocation-input-latitude').val();

        if (lng && lat) {
          that.locationAlteredCallback('widget-refreshed', {lat: Number(lat), lng: Number(lng)}, delta);
        }
        that.attachInputChangedTriggers(input, delta);
      });
    },
    getInputByDelta: function (delta) {
      delta = parseInt(delta) || 0;
      var input = this.getAllInputs().eq(delta);
      if (input.length) {
        return input;
      }
      return null;
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
    attachInputChangedTriggers: function(input, delta) {
      input = $(input);
      var that = this;
      var longitude = input.find('input.geolocation-input-longitude');
      var latitude = input.find('input.geolocation-input-latitude');

      longitude.off("change");
      longitude.change(function() {
        if (that.inputChangedEventPaused) {
          return;
        }

        var currentValue = $(this).val();
        if (currentValue === '') {
          that.locationAlteredCallback('input-altered', null, delta)
        }
        else if (latitude.val() !== '') {
          var location = {lat: Number(latitude.val()), lng: Number(currentValue)};
          that.locationAlteredCallback('input-altered', location, delta);
        }
      });

      latitude.off("change");
      latitude.change(function() {
        if (that.inputChangedEventPaused) {
          return;
        }

        var currentValue = $(this).val();
        if (currentValue === '') {
          that.locationAlteredCallback('input-altered', null, delta)
        }
        else if (longitude.val() !== '') {
          var location = {lat: Number(currentValue), lng: Number(longitude.val())};
          that.locationAlteredCallback('input-altered', location, delta);
        }
      });
    },
    addInput: function (location, delta) {
      if (typeof delta === 'undefined') {
        delta = this.getNextDelta();
      }

      if (
        typeof delta === 'undefined'
        || delta === false
      ) {
        console.error(location, Drupal.t('Could not determine delta for new widget input.'));
        return null;
      }

      var input = this.getInputByDelta(delta);
      if (input) {
        this.inputChangedEventPaused = true;
        input.find('input.geolocation-input-longitude').val(location.lng);
        input.find('input.geolocation-input-latitude').val(location.lat);
        this.inputChangedEventPaused = false;
      }

      return delta;
    },
    updateInput: function (location, delta) {
      var input = this.getInputByDelta(delta);
      this.inputChangedEventPaused = true;
      input.find('input.geolocation-input-longitude').val(location.lng);
      input.find('input.geolocation-input-latitude').val(location.lat);
      this.inputChangedEventPaused = false;
    },
    removeInput: function (delta) {
      var input = this.getInputByDelta(delta);
      this.inputChangedEventPaused = true;
      input.find('input.geolocation-input-longitude').val('');
      input.find('input.geolocation-input-latitude').val('');
      this.inputChangedEventPaused = false;
    }
  };

  Drupal.geolocation.widget.GeolocationMapWidgetBase = GeolocationMapWidgetBase;

  /**
   * Factory creating widget instances.
   *
   * @constructor
   * @param {GeolocationWidgetSettings} widgetSettings - The widget settings.
   * @param {Boolean} [reset] Force creation of new widget.
   * @return {GeolocationWidgetInterface|boolean} - New or updated widget.
   */
  function Factory(widgetSettings, reset) {
    reset = reset || false;
    widgetSettings.type = widgetSettings.type || 'google';

    var widget = null;

    /**
     * Previously stored widget.
     * @type {boolean|GeolocationWidgetInterface}
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

  Drupal.geolocation.widget.widgetProviders = {};

  Drupal.geolocation.widget.addWidgetProvider = function (type, name) {
    Drupal.geolocation.widget.widgetProviders[type] = name;
  };

  /**
   * Get widget by ID.
   *
   * @param {String} id - Widget ID to retrieve.
   * @return {GeolocationWidgetInterface|boolean} - Retrieved widget or false.
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
