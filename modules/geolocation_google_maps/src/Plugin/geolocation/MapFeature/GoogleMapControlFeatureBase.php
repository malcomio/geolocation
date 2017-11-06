<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\MapFeatureBase;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;

/**
 * Class GoogleMapControlFeatureBase.
 */
abstract class GoogleMapControlFeatureBase extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'position' => 'TOP_LEFT',
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $form = [];

    $settings = array_replace_recursive(
      self::getDefaultSettings(),
      $settings
    );

    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => GoogleMaps::getControlPositions(),
      '#default_value' => $settings['position'],
    ];

    $form['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#size' => 4,
      '#default_value' => $settings['weight'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSettingsForm(array $element, FormStateInterface $form_state, array $form) {
    $values = $form_state->getValues();
    $parents = [];
    if (!empty($element['#parents'])) {
      $parents = $element['#parents'];
      $values = NestedArray::getValue($values, $parents);
    }

    if (!in_array($values['position'], array_keys(GoogleMaps::getControlPositions()))) {
      $form_state->setErrorByName(implode('][', $parents), $this->t('No valid position.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array['#controls'][$this->pluginId] = [
      '#weight' => $settings['weight'],
      '#position' => $settings['position'],
    ];

    return $render_array;
  }

}
