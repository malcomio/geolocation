<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "client_location_indicator",
 *   name = @Translation("Google Client Location Indicator"),
 *   description = @Translation("Continuously show client location marker on map."),
 *   type = "google_maps",
 * )
 */
class GoogleClientLocationIndicator extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $maps_id) {
    return [
      'library' => [
        'geolocation_google_maps/geolocation.client_location_indicator',
      ],
      'drupalSettings' => [
        'geolocation' => [
          'maps' => [
            $maps_id => [
              'client_location_indicator' => [
                'enable' => TRUE,
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
