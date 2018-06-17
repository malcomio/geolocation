<?php

namespace Drupal\geolocation_geofield_leaflet\Plugin\Field\FieldFormatter;

use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_leaflet' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_geofield_leaflet",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Leaflet - Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class GeolocationGeofieldLeafletFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderId = 'leaflet';

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderSettingsFormId = 'leaflet_settings';

}
