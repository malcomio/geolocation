<?php

namespace Drupal\geolocation_geofield_google_maps\Plugin\Field\FieldFormatter;

use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_googlemap' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_geofield_google_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Google Maps API - Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class GeolocationGeofieldGoogleMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderId = 'google_maps';

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderSettingsFormId = 'google_map_settings';

}
