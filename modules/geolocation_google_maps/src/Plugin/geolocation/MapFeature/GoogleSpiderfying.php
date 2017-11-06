<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

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
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.spiderfying',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'spiderfying' => [
                  'enable' => TRUE,
                  'spiderfiable_marker_path' => base_path() . drupal_get_path('module', 'geolocation_google_maps') . '/images/marker-plus.svg',
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
