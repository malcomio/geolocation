<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

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
class ControlRecenter extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $render_array['#controls']['recenter'] = [
      '#type' => 'button',
      '#weight' => 100,
      '#value' => $this->t('Recenter'),
      '#attributes' => [
        'class' => ['recenter'],
      ],
    ];

    return $render_array;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $maps_id) {
    return [
      'library' => [
        'geolocation_google_maps/geolocation.control_recenter',
      ],
      'drupalSettings' => [
        'geolocation' => [
          'maps' => [
            $maps_id => [
              'control_recenter' => [
                'enable' => TRUE,
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
