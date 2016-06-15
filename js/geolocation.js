/**
 * @file
 *   Javascript for the geocoder module.
 */
(function ($, _, Drupal, settings) {

  'use strict';

  /* global google */

  // Ensure the geolocation object without overwriting it.
  var geolocation = Drupal.geolocation = Drupal.geolocation || {};
  // Google Maps are loaded lazily. In some situations load_google() is called twice, which results in
  // "You have included the Google Maps API multiple times on this page. This may cause unexpected errors." errors.
  // This flag will prevent repeat $.getScript() calls.
  geolocation.maps_api_loading = false;

  /**
   * Gets the default settings for the google map.
   *
   * @return {{scrollwheel: boolean, panControl: boolean, mapTypeControl: boolean, scaleControl: boolean, streetViewControl: boolean, overviewMapControl: boolean, zoomControl: boolean, zoomControlOptions: {style: *, position: *}, mapTypeId: *, zoom: number}}
   */
  geolocation.default_settings = function () {
    return {
      scrollwheel: false,
      panControl: false,
      mapTypeControl: true,
      scaleControl: false,
      streetViewControl: false,
      overviewMapControl: false,
      zoomControl: true,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle.LARGE,
        position: google.maps.ControlPosition.LEFT_TOP
      },
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoom: 2
    };
  };

  /**
   * Provides the callback that is called when maps loads.
   */
  geolocation.google_callback = function () {
    // Ensure callbacks array;
    geolocation.google_load_callbacks = geolocation.google_load_callbacks || [];

    // Wait until the window load event to try to use the maps library.
    $(document).ready(function (e) {
      _.invoke(settings.geolocation.google_load_callbacks, 'callback');
      geolocation.google_load_callbacks = [];
    });
  };

  /**
   * Adds a callback that will be called once the maps library is loaded.
   *
   * @param callback
   */
  geolocation.add_callback = function (callback) {
    settings.geolocation.google_load_callbacks = geolocation.google_load_callbacks || [];
    settings.geolocation.google_load_callbacks.push({callback: callback});
  };

  /**
   * Load google maps and set a callback to run when it's ready.
   *
   * @param callback
   */
  geolocation.load_google = function (callback) {
    // Default script path.
    var scriptPath = '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&callback=Drupal.geolocation.google_callback';

    // Add the callback.
    geolocation.add_callback(callback);
    // Check for google maps.
    if (!geolocation.maps_api_loading && (typeof google == 'undefined' || typeof google.maps == 'undefined')) {
      geolocation.maps_api_loading = true;
      // google maps isn't loaded so lazy load google maps.
      // If a Google API key isset, use it.
      if (settings.geolocation.google_map_api_key) {
        scriptPath += '&key=' + settings.geolocation.google_map_api_key;
      }

      $.getScript(scriptPath)
        .done(function () {
          geolocation.maps_api_loading = false;
        });

    }
    else {
      // Google maps loaded. Run callback.
      geolocation.google_callback();
    }
  };

  /**
   * Load google maps and set a callback to run when it's ready.
   *
   * @param map
   */
  geolocation.add_map = function (map) {
    // Set the container size.
    $(map.container).css({
      height: map.settings.height,
      width: map.settings.width
    });

    // Get the center point.
    var center = new google.maps.LatLng(map.lat, map.lng);

    // Create the map object and assign it to the map.
    map.google_map = new google.maps.Map(map.container, {
      zoom: parseInt(map.settings.zoom),
      center: center,
      mapTypeId: google.maps.MapTypeId[map.settings.type],
      zoomControl: map.settings.zoomControl,
      streetViewControl: map.settings.streetViewControl,
      mapTypeControl: map.settings.mapTypeControl,
      scrollwheel: map.settings.scrollwheel,
      disableDoubleClickZoom: map.settings.disableDoubleClickZoom,
      draggable: map.settings.draggable
    });

    // Set the map marker.
    if (map.lat !== '' && map.lng !== '') {
      geolocation.setMapMarker(center, map);
    }

    if (!geolocation.hasOwnProperty('maps')) {
      geolocation.maps = [];
    }

    geolocation.maps.push(map);
  };

  /**
   * Load google maps and set a callback to run when it's ready.
   *
   * @param map
   */
  geolocation.add_geocoder = function (map) {

    /**
     * Callback for geocoder controls click submit.
     *
     * @param e
     *   The event from input keypress or the click of the submit button.
     */
    var handleControlEvent = function (e) {
      if (typeof e.keyCode === 'undefined' || e.keyCode === 13 || e.keyCode === 0) {
        // We don't any forms submitting.
        e.preventDefault();
        // Get the address from the input value.
        var address = $(e.target).parent().children('input.input').val();
        // Make sure there are at least 2 characters for geocoding.
        if (address.length > 1) {
          // Run the geocode function with google maps.
          map.geocoder.geocode({address: address}, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
              // Set the map viewport.
              map.google_map.fitBounds(results[0].geometry.viewport);
              // Set the values for the field.
              geolocation.codeLatLng(results[0].geometry.location, map);
              // Set the map marker.
              geolocation.setMapMarker(results[0].geometry.location, map);
            }
            else {
              // Alert of the error geocoding.
              alert(Drupal.t('Geocode was not successful for the following reason: ') + status);
            }
          });
        }
      }
    };

    map.geocoder = new google.maps.Geocoder();
    map.controls = $('<div class="geocode-controlls-wrapper" />')
      .append($('<input type="text" class="input" placeholder="Enter a location" />'))
      // Create submit button
      .append($('<button class="submit" />'))
      // Create clear button
      .append($('<button class="clear" />'))
      // Create clear button
      .append($('<div class="geolocation-map-indicator" />'))
      // Use the DOM element.
      .get(0);

    // Add the default indicator if the values aren't blank.
    if (map.lat !== '' && map.lng !== '') {
      $(map.controls).children('.geolocation-map-indicator')
        .addClass('has-location')
        .text(map.lat + ', ' + map.lng);
    }

    map.controls.index = 1;

    map.google_map.controls[google.maps.ControlPosition.TOP_LEFT].push(map.controls);

    // Add the listened for the search click event.
    google.maps.event.addDomListener($(map.controls).children('button.submit')[0], 'click', handleControlEvent);
    // Add the listened for the search click event.
    google.maps.event.addDomListener($(map.controls).children('input.input')[0], 'keyup', handleControlEvent);
    // Add the event listener for the remove button.
    google.maps.event.addDomListener($(map.controls).children('button.clear')[0], 'click', function (e) {
      // Stop all that bubbling and form submitting.
      e.preventDefault();
      // Remove the coordinates.
      $(map.controls).children('.geolocation-map-indicator').text('').removeClass('has-location');
      // Clear the map point.
      map.marker.setMap();
      // Clear the input text.
      $(map.controls).children('input.input').val('');
      // Remove the form values.
      // Update the lat and lng input fields
      $('.geolocation-hidden-lat.for-' + map.id).attr('value', '');
      $('.geolocation-hidden-lng.for-' + map.id).attr('value', '');
    });
  };

  /**
   * Set the latitude and longitude values to the input fields
   * And optionally update the address field
   *
   * @param latLng
   *   a location (latLng) object from google maps api
   * @param map
   *   The settings object that contains all of the necessary metadata for this map.
   * @param op
   *   the op that was performed
   */
  geolocation.codeLatLng = function (latLng, map) {
    // Update the lat and lng input fields
    $('.geolocation-hidden-lat.for-' + map.id).attr('value', latLng.lat());
    $('.geolocation-hidden-lng.for-' + map.id).attr('value', latLng.lng());
  };

  /**
   * Set/Update a marker on a map
   *
   * @param latLng
   *   a location (latLng) object from google maps api
   * @param map
   *   The settings object that contains all of the necessary metadata for this map.
   */
  geolocation.setMapMarker = function (latLng, map) {
    // make sure the marker exists.
    if (typeof map.marker !== 'undefined') {
      map.marker.setPosition(latLng);
      map.marker.setMap(map.google_map);
    }
    else {

      // Set the info popup text.
      map.infowindow = new google.maps.InfoWindow({
        content: map.settings.info_text
      });

      // Add the marker to the map.
      map.marker = new google.maps.Marker({
        position: latLng,
        map: map.google_map,
        title: map.settings.title,
        label: map.settings.label
      });

      // Add the info window event if the info text has been set.
      if (map.settings.info_text && map.settings.info_text.length > 0) {
        map.marker.addListener('click', function () {
          map.infowindow.open(map.google_map, map.marker);
        });
        if (map.settings.info_auto_display) {
          map.infowindow.open(map.google_map, map.marker);
        }
      }
    }

    // Add a visual indicator.
    $(map.controls).children('.geolocation-map-indicator')
      .text(latLng.lat() + ', ' + latLng.lng())
      .addClass('has-location');
  };

})(jQuery, _, Drupal, drupalSettings);
