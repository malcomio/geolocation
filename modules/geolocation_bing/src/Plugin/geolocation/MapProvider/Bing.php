<?php


namespace Drupal\geolocation_bing\Plugin\geolocation\MapProvider;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\geolocation\MapProviderBase;

/**
 * Class Bing.
 *
 * @MapProvider(
 *   id = "bing",
 *   name = @Translation("Bing maps"),
 *   description = @Translation("Bing maps support."),
 * )
 */
class Bing extends MapProviderBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {

    $config = \Drupal::config('geolocation_bing.settings');

    $defaultSettings = parent::getDefaultSettings();
    return array_replace_recursive(
      $defaultSettings,
      [
        'zoom' => 10,
        'height' => '400px',
        'width' => '100%',
        'map_features' => [
          'bing_marker_infobox' => [
            'enabled' => TRUE,
          ],
        ],
        'api_key' => $config->get('api_key'),
        'icon_path' => '',
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $settings = parent::getSettings($settings);

    $settings['zoom'] = (int) $settings['zoom'];
    $settings['icon_path'] = $settings['icon_path'];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $settings = array_replace_recursive(
      self::getDefaultSettings(),
      $settings
    );
    $summary = parent::getSettingsSummary($settings);
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
    if ($parents) {
      $parents_string = implode('][', $parents);
    }
    else {
      $parents_string = NULL;
    }

    $form = parent::getSettingsForm($settings, $parents);

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
      '#group' => $parents_string,
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => range(0, 20),
      '#description' => $this->t('The initial resolution at which to display the map, where zoom 0 corresponds to a map of the Earth fully zoomed out, and higher zoom levels zoom in at a higher resolution.'),
      '#default_value' => $settings['zoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];

    $form['icon_path'] = [
      '#group' => $parents_string,
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#description' => $this->t('Set relative or absolute path to custom marker icon. Empty for default.'),
      '#default_value' => $settings['icon_path'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $map_settings, array $context = []) {

    $map_settings = $this->getSettings($map_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_bing/geolocation.bing',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'settings' => [
                  'bing_settings' => $map_settings,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    $render_array = parent::alterRenderArray($render_array, $map_settings, $context);

    return $render_array;
  }

  /**
   * {@inheritdoc}
   */
  public static function getControlPositions() {
    return [
      'topleft' => t('Top left'),
      'topright' => t('Top right'),
      'bottomleft' => t('Bottom left'),
      'bottomright' => t('Bottom right'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function alterCommonMap(array $render_array, array $map_settings, array $context) {
    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_bing/commonmap.bing',
        ],
      ]
    );

    return $render_array;
  }

}
