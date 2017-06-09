<?php

namespace Drupal\geolocation\Plugin\geolocation\MapProvider;

use Drupal\geolocation\MapProviderBase;

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
   * Provide a populated settings array.
   *
   * @return array
   *   The settings array with the default map settings.
   */
  public static function getDefaultSettings() {
    return [
      'leaflet_settings' => [
        'zoom' => 10,
        'height' => '400px',
        'width' => '100%',
      ],
    ];
  }

  /**
   * Provide settings ready to handover to JS to feed to Leaflet.
   *
   * @param array $settings
   *   Current settings. Might contain unrelated settings as well.
   *
   * @return array
   *   An array only containing keys defined in this trait.
   */
  public function getSettings(array $settings) {
    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    foreach ($settings['leaflet_settings'] as $key => $setting) {
      if (!isset($default_settings['leaflet_settings'][$key])) {
        unset($settings['leaflet_settings'][$key]);
      }
    }

    return [
      'leaflet_settings' => $settings['leaflet_settings'],
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
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('Zoom level: @zoom', ['@zoom' => $settings['leaflet_settings']['zoom']]);
    $summary[] = $this->t('Height: @height', ['@height' => $settings['leaflet_settings']['height']]);
    $summary[] = $this->t('Width: @width', ['@width' => $settings['leaflet_settings']['width']]);
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
  public function getSettingsForm(array $settings, $form_prefix = '') {
    $settings['leaflet_settings'] += self::getDefaultSettings()['leaflet_settings'];
    $form = [
      'leaflet_settings' => [
        '#type' => 'details',
        '#title' => t('Leaflet settings'),
        '#description' => t('Additional map settings provided by Leaflet'),
      ],
    ];
    $form['leaflet_settings']['height'] = [
      '#group' => $form_prefix . 'leaflet_settings][general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['leaflet_settings']['height'],
    ];
    $form['leaflet_settings']['width'] = [
      '#group' => $form_prefix . 'leaflet_settings][general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['leaflet_settings']['width'],
    ];
    $form['leaflet_settings']['zoom'] = [
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => range(0, 20),
      '#description' => $this->t('The initial resolution at which to display the map, where zoom 0 corresponds to a map of the Earth fully zoomed out, and higher zoom levels zoom in at a higher resolution.'),
      '#default_value' => $settings['leaflet_settings']['zoom'],
      '#group' => $form_prefix . 'leaflet_settings][general_settings',
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
  public function getLibraries() {
    return ['geolocation/geolocation.leaflet'];
  }

}
