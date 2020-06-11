<?php

namespace Drupal\geolocation_bing\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides marker popup.
 *
 * @MapFeature(
 *   id = "bing_marker_infobox",
 *   name = @Translation("Infobox"),
 *   description = @Translation("Open infobox on Marker click."),
 *   type = "bing",
 * )
 */
class BingInfobox extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'info_auto_display' => FALSE,
      'infobox_solitary' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = $this->getSettings($settings);

    $form['infobox_solitary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only allow one current open infobox.'),
      '#description' => $this->t('If checked, clicking a marker will close the current infobox before opening a new one.'),
      '#default_value' => $settings['infobox_solitary'],
    ];

    $form['info_auto_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically show info text.'),
      '#default_value' => $settings['info_auto_display'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings, array $context = []) {
    $render_array = parent::alterMap($render_array, $feature_settings, $context);

    $feature_settings = $this->getSettings($feature_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_bing/mapfeature.' . $this->getPluginId(),
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                $this->getPluginId() => [
                  'enable' => TRUE,
                  'infoAutoDisplay' => $feature_settings['info_auto_display'],
                  'infoWindowSolitary' => $feature_settings['infobox_solitary'],
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
