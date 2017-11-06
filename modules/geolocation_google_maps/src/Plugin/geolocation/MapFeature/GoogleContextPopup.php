<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "context_popup",
 *   name = @Translation("Context Popup"),
 *   description = @Translation("Provide right-click popup context windows."),
 *   type = "google_maps",
 * )
 */
class GoogleContextPopup extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'content' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('ContextPopup enabled');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = $this->getSettings($settings);
    $form['content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Context popup content'),
      '#description' => $this->t('A right click on the map will open a context popup with this content. Tokens supported. Additionally "@lat, @lng" will be replaced dynamically.'),
      '#default_value' => $settings['content'],
    ];

    if (\Drupal::service('module_handler')->moduleExists('token')) {
      // Add the token UI from the token module if present.
      $form['token_help'] = [
        '#theme' => 'token_tree_link',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $settings = $this->getSettings($settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.contextpopup',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'context_popup' => [
                  'enable' => TRUE,
                  'content' => $settings['content'],
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
