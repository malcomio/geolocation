<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "spiderfying",
 *   name = @Translation("Google Spiderfying"),
 *   description = @Translation("Split up overlapping markers on click."),
 *   type = "google_maps",
 * )
 */
class GoogleSpiderfying extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $maps_id) {
    return [
      'library' => [
        'geolocation_google_maps/geolocation.spiderfying',
      ],
      'drupalSettings' => [
        'geolocation' => [
          'maps' => [
            $maps_id => [
              'spiderfying' => [
                'enable' => TRUE,
                'spiderfiable_marker_path' => base_path() . drupal_get_path('module', 'geolocation_google_maps') . '/images/marker-plus.svg',
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
