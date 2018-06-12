<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldFormatter;

use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_googlemap' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Google Maps API - Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 *
 * @property \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps $mapProvider
 */
class GeolocationGoogleMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderId = 'google_maps';

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderSettingsFormId = 'google_map_settings';

}
