<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "marker_icon",
 *   name = @Translation("Marker Icon"),
 *   description = @Translation("OIcon properties."),
 *   type = "google_maps",
 * )
 */
class GoogleMarkerIcon extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'anchor' => [
        'x' => 0,
        'y' => 0,
      ],
      'origin' => [
        'x' => 0,
        'y' => 0,
      ],
      'label_origin' => [
        'x' => 0,
        'y' => 0,
      ],
      'size' => [
        'width' => NULL,
        'height' => NULL,
      ],
      'scaled_size' => [
        'width' => NULL,
        'height' => NULL,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('InfoWindow enabled');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = $this->getSettings($settings);

    $form['anchor'] = [
      '#type' => 'item',
      '#title' => $this->t('Anchor'),
      '#description' => $this->t('The position at which to anchor an image in correspondence to the location of the marker on the map. By default, the anchor is located along the center point of the bottom of the image.'),
      'x' => [
        '#type' => 'number',
        '#title' => $this->t('X'),
        '#default_value' => $settings['anchor']['x'],
        '#min' => 0,
      ],
      'y' => [
        '#type' => 'number',
        '#title' => $this->t('Y'),
        '#default_value' => $settings['anchor']['y'],
        '#min' => 0,
      ],
    ];
    $form['origin'] = [
      '#type' => 'item',
      '#title' => $this->t('Origin'),
      '#description' => $this->t('The position of the image within a sprite, if any. By default, the origin is located at the top left corner of the image (0, 0).'),
      'x' => [
        '#type' => 'number',
        '#title' => $this->t('X'),
        '#default_value' => $settings['origin']['x'],
        '#min' => 0,
      ],
      'y' => [
        '#type' => 'number',
        '#title' => $this->t('Y'),
        '#default_value' => $settings['origin']['y'],
        '#min' => 0,
      ],
    ];
    $form['label_origin'] = [
      '#type' => 'item',
      '#title' => $this->t('Label Origin'),
      '#description' => $this->t('The origin of the label relative to the top-left corner of the icon image, if a label is supplied by the marker. By default, the origin is located in the center point of the image.'),
      'x' => [
        '#type' => 'number',
        '#title' => $this->t('X'),
        '#default_value' => $settings['label_origin']['x'],
        '#min' => 0,
      ],
      'y' => [
        '#type' => 'number',
        '#title' => $this->t('Y'),
        '#default_value' => $settings['label_origin']['y'],
        '#min' => 0,
      ],
    ];
    $form['size'] = [
      '#type' => 'item',
      '#title' => $this->t('Size'),
      '#description' => $this->t('The display size of the sprite or image. When using sprites, you must specify the sprite size. If the size is not provided, it will be set when the image loads.'),
      'width' => [
        '#type' => 'number',
        '#title' => $this->t('Width'),
        '#default_value' => $settings['size']['width'],
        '#min' => 0,
      ],
      'height' => [
        '#type' => 'number',
        '#title' => $this->t('Height'),
        '#default_value' => $settings['size']['height'],
        '#min' => 0,
      ],
    ];
    $form['scaled_size'] = [
      '#type' => 'item',
      '#title' => $this->t('Scaled Size'),
      '#description' => $this->t('The size of the entire image after scaling, if any. Use this property to stretch/shrink an image or a sprite.'),
      'width' => [
        '#type' => 'number',
        '#title' => $this->t('Width'),
        '#default_value' => $settings['scaled_size']['width'],
        '#min' => 0,
      ],
      'height' => [
        '#type' => 'number',
        '#title' => $this->t('Height'),
        '#default_value' => $settings['scaled_size']['height'],
        '#min' => 0,
      ],
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
          'geolocation_google_maps/geolocation.markericon',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'marker_icon' => [
                  'enable' => TRUE,
                  'anchor' => $feature_settings['anchor'],
                  'size' => $feature_settings['size'],
                  'scaledSize' => $feature_settings['scaled_size'],
                  'labelOrigin' => $feature_settings['label_origin'],
                  'origin' => $feature_settings['origin'],
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
