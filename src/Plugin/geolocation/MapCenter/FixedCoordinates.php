<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Fixed coordinates map center.
 *
 * PluginID for compatibility with v1.
 *
 * @MapCenter(
 *   id = "fixed_value",
 *   name = @Translation("Fixed coordinates"),
 *   description = @Translation("Use preset fixed values as center."),
 * )
 */
class FixedCoordinates extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'latitude' => '',
      'longitude' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm($option_id = NULL, array $context = [], array $settings = []) {
    $settings = $this->getSettings($settings);

    $form = [
      'latitude' => [
        '#type' => 'textfield',
        '#title' => $this->t('Latitude'),
        '#default_value' => $settings['latitude'],
        '#size' => 60,
        '#maxlength' => 128,
      ],
      'longitude' => [
        '#type' => 'textfield',
        '#title' => $this->t('Longitude'),
        '#default_value' => $settings['longitude'],
        '#size' => 60,
        '#maxlength' => 128,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    $settings = $this->getSettings($center_option_settings);

    return array_replace_recursive(
      parent::getMapCenter($center_option_id, $center_option_settings, $context),
      [
        'lat' => (float) $settings['latitude'],
        'lng' => (float) $settings['longitude'],
        'behavior' => 'preset',
      ]
    );
  }

}
