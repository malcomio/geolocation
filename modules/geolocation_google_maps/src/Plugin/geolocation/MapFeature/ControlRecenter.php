<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "control_recenter",
 *   name = @Translation("Control Recenter"),
 *   description = @Translation("Add button to recenter map."),
 *   type = "google_maps",
 * )
 */
class ControlRecenter extends GoogleMapControlFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.control_recenter',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'control_recenter' => [
                  'enable' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    $render_array['#controls'][$this->pluginId] = NestedArray::mergeDeep(
      $render_array['#controls'][$this->pluginId],
      [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#value' => $this->t('Recenter'),
        '#attributes' => [
          'class' => ['recenter'],
        ],
      ]
    );

    return $render_array;
  }

}
