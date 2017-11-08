<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides continious client location indicator.
 *
 * @MapFeature(
 *   id = "client_location_indicator",
 *   name = @Translation("Client Location Indicator"),
 *   description = @Translation("Continuously show client location marker on map."),
 *   type = "google_maps",
 * )
 */
class ClientLocationIndicator extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.client_location_indicator',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'client_location_indicator' => [
                  'enable' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    return $render_array;
  }

}
