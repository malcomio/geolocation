<?php

namespace Drupal\geolocation_google_static_maps\Plugin\geolocation\MapProvider;

use Drupal\geolocation_google_maps\GoogleMapsProviderBase;

/**
 * Provides Google Maps.
 *
 * @MapProvider(
 *   id = "google_static_maps",
 *   name = @Translation("Google Static Maps"),
 *   description = @Translation("You do require an API key for this plugin to work."),
 * )
 */
class GoogleStaticMaps extends GoogleMapsProviderBase {

  /**
   * {@inheritdoc}
   */
  public static $GOOGLEMAPSAPIURLPATH = '/maps/api/staticmap';

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return array_replace_recursive(
      parent::getDefaultSettings(),
      [
        'height' => '400',
        'width' => '400',
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $form = parent::getSettingsForm($settings, $parents);
    $parents_string = '';
    if ($parents) {
      $parents_string = implode('][', $parents) . '][';
    }

    $form['width'] = array_replace($form['width'], [
      '#type' => 'number',
      '#description' => $this->t('Enter width in pixels. Free users maximum 640.'),
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\Number', 'preRenderNumber'],
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ]);
    $form['height'] = array_replace($form['height'], [
      '#type' => 'number',
      '#description' => $this->t('Enter height in pixels. Free users maximum 640.'),
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\Number', 'preRenderNumber'],
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ]);

    $form['scale'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Scale Value'),
      '#options' => [
        '1' => $this->t('1 (default)'),
        '2' => $this->t('2'),
        '4' => $this->t('4 - Google Maps APIs Premium Plan only'),
      ],
      '#default_value' => $settings['scale'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];

    $form['format'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Image Format'),
      '#options' => [
        'png' => $this->t('8-bit PNG (default)'),
        'png32' => $this->t('32-bit PNG'),
        'gif' => $this->t('GIF'),
        'jpg' => $this->t('JPEG'),
        'jpg-baseline' => $this->t('non-progressive JPEG'),
      ],
      '#default_value' => $settings['format'],
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
}
