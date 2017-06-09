<?php

namespace Drupal\geolocation;

/**
 * Class CommonGoogleMapDrawingTrait.
 *
 * @package Drupal\geolocation
 */
trait CommonGoogleMapDrawingTrait {

  /**
   * Provide a populated settings array.
   *
   * @return array
   *   The settings array with the default map settings.
   */
  public static function getCommonGoogleMapDrawingDefaultSettings() {
    return [
      'common_google_map_drawing_settings' => [
        'polyline' => FALSE,
        'strokeColor' => '#FF0000',
        'strokeOpacity' => 0.8,
        'strokeWeight' => 2,
        'geodesic' => FALSE,
        'polygon' => FALSE,
        'fillColor' => '#FF0000',
        'fillOpacity' => 0.35,
      ],
    ];
  }

  /**
   * Provide settings ready to handover to JS to feed to Google Maps.
   *
   * @param array $settings
   *   Current settings. Might contain unrelated settings as well.
   *
   * @return array
   *   An array only containing keys defined in this trait.
   */
  public function getCommonGoogleMapDrawingSettings(array $settings) {
    $default_settings = self::getCommonGoogleMapDrawingDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    foreach ($settings['common_google_map_drawing_settings'] as $key => $setting) {
      if (!isset($default_settings['common_google_map_drawing_settings'][$key])) {
        unset($settings['common_google_map_drawing_settings'][$key]);
      }
    }

    return [
      'common_google_map_drawing_settings' => $settings['common_google_map_drawing_settings'],
    ];
  }

  /**
   * Provide a summary array to use in field formatters.
   *
   * @param array $settings
   *   The current map settings.
   *
   * @return array
   *   An array to use as field formatter summary.
   */
  public function getCommonGoogleMapDrawingSettingsSummary(array $settings) {
    $summary = [];

    $summary[] = $this->t('Draw polyline: @polyline', ['@polyline' => $settings['common_google_map_drawing_settings']['polyline'] ? $this->t('Yes') : $this->t('No')]);
    $summary[] = $this->t('Draw polygon: @polygon', ['@polygon' => $settings['common_google_map_drawing_settings']['polygon'] ? $this->t('Yes') : $this->t('No')]);
    return $summary;
  }

  /**
   * Provide a generic map settings form array.
   *
   * @param array $settings
   *   The current map settings.
   * @param string $form_prefix
   *   Form specific optional prefix.
   *
   * @return array
   *   A form array to be integrated in whatever.
   */
  public function getCommonGoogleMapDrawingSettingsForm(array $settings, $form_prefix = '') {
    $settings['common_google_map_drawing_settings'] += self::getCommonGoogleMapDrawingDefaultSettings()['common_google_map_drawing_settings'];

    $form = [
      'common_google_map_drawing_settings' => [
        '#type' => 'details',
        '#title' => t('CommonMap drawing settings'),
        '#description' => t('Additional CommonMap settings related to draw operations.'),
      ],
    ];

    $form['common_google_map_drawing_settings']['polyline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Draw polyline'),
      '#description' => $this->t('A polyline is a linear overlay of connected line segments on the map.'),
      '#default_value' => $settings['common_google_map_drawing_settings']['polyline'],
    ];
    $form['common_google_map_drawing_settings']['strokeColor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stroke color'),
      '#description' => $this->t('The stroke color. All CSS3 colors are supported except for extended named colors.'),
      '#size' => 4,
      '#default_value' => $settings['common_google_map_drawing_settings']['strokeColor'],
      '#states' => [
        'visible' => [
          [
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polyline]"]' => ['checked' => TRUE]],
            'or',
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
    ];
    $form['common_google_map_drawing_settings']['strokeOpacity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stroke opacity'),
      '#description' => $this->t('The stroke opacity between 0.0 and 1.0.'),
      '#size' => 2,
      '#default_value' => $settings['common_google_map_drawing_settings']['strokeOpacity'],
      '#states' => [
        'visible' => [
          [
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polyline]"]' => ['checked' => TRUE]],
            'or',
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
    ];
    $form['common_google_map_drawing_settings']['strokeWeight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Stroke weight'),
      '#description' => $this->t('The stroke width in pixels.'),
      '#size' => 2,
      '#default_value' => $settings['common_google_map_drawing_settings']['strokeWeight'],
      '#states' => [
        'visible' => [
          [
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polyline]"]' => ['checked' => TRUE]],
            'or',
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
    ];
    $form['common_google_map_drawing_settings']['geodesic'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Geodesic lines'),
      '#description' => $this->t('When true, edges of the polygon are interpreted as geodesic and will follow the curvature of the Earth. When false, edges of the polygon are rendered as straight lines in screen space.'),
      '#default_value' => $settings['common_google_map_drawing_settings']['geodesic'],
      '#states' => [
        'visible' => [
          [
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polyline]"]' => ['checked' => TRUE]],
            'or',
            ['input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE]],
          ],
        ],
      ],
    ];

    $form['common_google_map_drawing_settings']['polygon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Draw polygon'),
      '#description' => $this->t('Polygons form a closed loop and define a filled region.'),
      '#default_value' => $settings['common_google_map_drawing_settings']['polygon'],
    ];
    $form['common_google_map_drawing_settings']['fillColor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fill color'),
      '#description' => $this->t('The fill color. All CSS3 colors are supported except for extended named colors.'),
      '#size' => 4,
      '#default_value' => $settings['common_google_map_drawing_settings']['fillColor'],
      '#states' => [
        'visible' => [
          'input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['common_google_map_drawing_settings']['fillOpacity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fill opacity'),
      '#description' => $this->t('The fill opacity between 0.0 and 1.0.'),
      '#size' => 4,
      '#default_value' => $settings['common_google_map_drawing_settings']['fillOpacity'],
      '#states' => [
        'visible' => [
          'input[name="' . $form_prefix . 'common_google_map_drawing_settings][polygon]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

}
