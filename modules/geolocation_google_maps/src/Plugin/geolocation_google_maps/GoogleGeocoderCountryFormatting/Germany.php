<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation_google_maps\GoogleGeocoderCountryFormatting;

use Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingInterface;
use Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingBase;

/**
 * Provides germany address formatting.
 *
 * @GoogleGeocoderCountryFormatting(
 *   id = "de",
 * )
 */
class Germany extends GoogleGeocoderCountryFormattingBase implements GoogleGeocoderCountryFormattingInterface {

  /**
   * {@inheritdoc}
   */
  public function format(array $atomics) {
    $address_elements = parent::format($atomics);
    if (
      $atomics['streetNumber']
      && $atomics['route']
    ) {
      $address_elements['addressLine1'] = $atomics['route'] . ' ' . $atomics['streetNumber'];
    }

    return $address_elements;
  }

}
