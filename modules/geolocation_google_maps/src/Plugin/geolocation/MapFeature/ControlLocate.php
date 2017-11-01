<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "control_locate",
 *   name = @Translation("Control Locate"),
 *   description = @Translation("Add button to center on client location."),
 *   type = "google_maps",
 * )
 */
class ControlLocate extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $render_array['#controls']['locate'] = [
      '#type' => 'button',
      '#weight' => -50,
      '#value' => $this->t('Locate'),
      '#attributes' => [
        'class' => ['locate'],
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
        'geolocation_google_maps/geolocation.control_locate',
      ],
      'drupalSettings' => [
        'geolocation' => [
          'maps' => [
            $maps_id => [
              'control_locate' => [
                'enable' => TRUE,
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
