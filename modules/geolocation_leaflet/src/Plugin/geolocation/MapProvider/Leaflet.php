<?php

namespace Drupal\geolocation_leaflet\Plugin\geolocation\MapProvider;

use Drupal\geolocation\MapProviderBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Google Maps.
 *
 * @MapProvider(
 *   id = "leaflet",
 *   name = @Translation("Leaflet"),
 *   description = @Translation("Leaflet support."),
 * )
 */
class Leaflet extends MapProviderBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'zoom' => 10,
      'height' => '400px',
      'width' => '100%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    foreach ($settings as $key => $setting) {
      if (!isset($default_settings[$key])) {
        unset($settings[$key]);
      }
    }

    foreach ($this->mapFeatureManager->getMapFeaturesByMapType('leaflet') as $feature_id => $feature_definition) {
      if (!empty($settings['map_features'][$feature_id]['enabled'])) {
        $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
        $settings['map_features'][$feature_id] = $feature->getSettings($settings['map_features'][$feature_id]['settings']);
      }
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('Zoom level: @zoom', ['@zoom' => $settings['zoom']]);
    $summary[] = $this->t('Height: @height', ['@height' => $settings['height']]);
    $summary[] = $this->t('Width: @width', ['@width' => $settings['width']]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $settings += self::getDefaultSettings();
    $parents_string = '';
    if ($parents) {
      $parents_string = implode('][', $parents);
    }
    $form = [
      '#type' => 'details',
      '#title' => t('Leaflet settings'),
      '#description' => t('Additional map settings provided by Leaflet'),
    ];
    $form['height'] = [
      '#group' => $parents_string,
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['height'],
    ];
    $form['width'] = [
      '#group' => $parents_string,
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['width'],
    ];
    $form['zoom'] = [
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => range(0, 20),
      '#description' => $this->t('The initial resolution at which to display the map, where zoom 0 corresponds to a map of the Earth fully zoomed out, and higher zoom levels zoom in at a higher resolution.'),
      '#default_value' => $settings['zoom'],
      '#group' => $parents_string,
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $map_id) {
    $settings = $this->getSettings($settings);

    $attachments = [
      'library' => [
        'geolocation_leaflet/geolocation.leaflet',
      ],
    ];

    foreach ($this->mapFeatureManager->getMapFeaturesByMapType('leaflet') as $feature_id => $feature_definition) {
      if (!empty($settings['map_features'][$feature_id]['enabled'])) {
        $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
        if ($feature) {
          $attachments = BubbleableMetadata::mergeAttachments($feature->attachments($settings['map_features'][$feature_id]['settings'] ?: [], $map_id), $attachments);
          unset($settings[$feature_id]);
        }
      }
    }

    $attachments = BubbleableMetadata::mergeAttachments(
      $attachments,
      [
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'settings' => $settings,
              ],
            ],
          ],
        ],
      ]
    );

    return $attachments;
  }

}
