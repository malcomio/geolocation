<?php

namespace Drupal\geolocation_address_leaflet\Plugin\Field\FieldFormatter;

use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_leaflet' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_address_leaflet",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Leaflet - Map"),
 *   field_types = {
 *     "address"
 *   }
 * )
 */
class GeolocationAddressLeafletFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderId = 'leaflet';

  /**
   * {@inheritdoc}
   */
  static protected $mapProviderSettingsFormId = 'leaflet_settings';

}
