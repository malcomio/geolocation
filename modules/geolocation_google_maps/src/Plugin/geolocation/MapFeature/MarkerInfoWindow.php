<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides marker infowindow.
 *
 * @MapFeature(
 *   id = "marker_infowindow",
 *   name = @Translation("Marker InfoWindow"),
 *   description = @Translation("Open InfoWindow on Marker click."),
 *   type = "google_maps",
 * )
 */
class MarkerInfoWindow extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'info_auto_display' => FALSE,
      'disable_auto_pan' => TRUE,
      'info_window_solitary' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = $this->getSettings($settings);

    $form['info_window_solitary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only allow one current open info window.'),
      '#description' => $this->t('If checked, clicking a marker will close the current info window before opening a new one.'),
      '#default_value' => $settings['info_window_solitary'],
    ];

    $form['info_auto_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically show info text.'),
      '#default_value' => $settings['info_auto_display'],
    ];
    $form['disable_auto_pan'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable automatic panning of map when info bubble is opened.'),
      '#default_value' => $settings['disable_auto_pan'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings) {
    $render_array = parent::alterMap($render_array, $feature_settings);

    $feature_settings = $this->getSettings($feature_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.markerinfowindow',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'marker_infowindow' => [
                  'enable' => TRUE,
                  'infoAutoDisplay' => $feature_settings['info_auto_display'],
                  'disableAutoPan' => $feature_settings['disable_auto_pan'],
                  'infoWindowSolitary' => $feature_settings['info_window_solitary'],
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
