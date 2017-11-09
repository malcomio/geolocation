<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides context popup.
 *
 * @MapFeature(
 *   id = "context_popup",
 *   name = @Translation("Context Popup"),
 *   description = @Translation("Provide context / right-click popup window."),
 *   type = "google_maps",
 * )
 */
class ContextPopup extends MapFeatureBase {

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
  public function alterMap(array $render_array, array $feature_settings) {
    $render_array = parent::alterMap($render_array, $feature_settings);

    $feature_settings = $this->getSettings($feature_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.contextpopup',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'context_popup' => [
                  'enable' => TRUE,
                  'content' => $feature_settings['content'],
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
