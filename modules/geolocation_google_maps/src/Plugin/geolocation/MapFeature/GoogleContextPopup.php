<?php

namespace Drupal\geolocation\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

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
      'image_path' => '',
      'styles' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    $return = [];

    $return['enable'] = TRUE;
    $return['imagePath'] = $settings['image_path'];
    $return['styles'] = json_decode($settings['styles']);

    return [
      'markerClusterer' => $return,
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
  public function getSettingsForm(array $settings, $form_prefix = '') {
    $form['context_popup_content'] = [
      '#group' => 'style_options][advanced_settings',
      '#type' => 'textarea',
      '#title' => $this->t('Context popup content'),
      '#description' => $this->t('A right click on the map will open a context popup with this content. Tokens supported. Additionally "@lat, @lng" will be replaced dynamically.'),
      '#default_value' => $this->options['context_popup_content'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {

    /*
     * Context popup content.
     */
    if (!empty($this->options['context_popup_content'])) {
      $context_popup_content = \Drupal::token()->replace($this->options['context_popup_content']);
      $build['#attached']['drupalSettings']['geolocation']['commonMap'][$map_id]['contextPopupContent'] = [];
      $build['#attached']['drupalSettings']['geolocation']['commonMap'][$map_id]['contextPopupContent']['enable'] = TRUE;
      $build['#attached']['drupalSettings']['geolocation']['commonMap'][$map_id]['contextPopupContent']['content'] = $context_popup_content;
    }
    return ['geolocation/geolocation.markerclusterer'];
  }

}
