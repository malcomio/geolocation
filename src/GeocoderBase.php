<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GeocoderBase.
 *
 * @package Drupal\geolocation
 */
abstract class GeocoderBase extends PluginBase implements GeocoderInterface {

  /**
   * Return plugin default settings.
   *
   * @return array
   *   Default settings.
   */
  protected function getDefaultSettings() {
    return [
      'label' => $this->t('Address'),
      'description' => $this->t('Enter an address to be localized.'),
    ];
  }

  /**
   * Return plugin settings.
   *
   * @return array
   *   Settings.
   */
  public function getSettings() {
    return array_replace_recursive($this->getDefaultSettings(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {
    $settings = $this->getSettings();

    return [
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $settings['label'],
        '#size' => 15,
      ],

      'description' => [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#default_value' => $settings['description'],
        '#size' => 25,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processOptionsForm(array $form_element) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    $settings = $this->getSettings();

    $render_array['geolocation_geocoder_address'] = [
      '#type' => 'search',
      '#title' => $settings['label'] ?: $this->t('Address'),
      '#placeholder' => $settings['label'] ?: $this->t('Address'),
      '#description' => $settings['description'] ?: $this->t('Enter an address to retrieve location.'),
      '#description_display' => 'after',
      '#maxlength' => 256,
      '#size' => 25,
      '#attributes' => [
        'class' => [
          'geolocation-geocoder-address',
          'form-autocomplete',
        ],
        'data-source-identifier' => $element_name,
      ],
      '#attached' => [
        'drupalSettings' => [
          'geolocation' => [
            'geocoder' => [
              $this->getPluginId() => [
                'inputIds' => [
                  $element_name,
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $render_array['geolocation_geocoder_state'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
      '#attributes' => [
        'class' => [
          'geolocation-geocoder-state',
        ],
        'data-source-identifier' => $element_name,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formValidateInput(FormStateInterface $form_state) {
    $validate = TRUE;

    $input = $form_state->getUserInput();
    if (
      !empty($input['geolocation_geocoder_address'])
      && empty($input['geolocation_geocoder_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_address']);

      if (empty($location_data)) {
        $form_state->setErrorByName('geolocation_geocoder_address', $this->t('Failed to geocode %input.', ['%input' => $input['geolocation_geocoder_address']]));
        return FALSE;
      }
    }
    return $validate;
  }

  /**
   * {@inheritdoc}
   */
  public function formProcessInput(array &$input, $element_name) {
    $return = TRUE;
    if (empty($input['geolocation_geocoder_address'])) {
      $return = FALSE;
    }

    if (
      !empty($input['geolocation_geocoder_address'])
      && empty($input['geolocation_geocoder_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_address']);

      if (empty($location_data)) {
        $input['geolocation_geocoder_state'] = 0;
        return FALSE;
      }

      $input['geolocation_geocoder_address'] = $location_data['address'];
      $input['geolocation_geocoder_state'] = 1;

      return $location_data;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {
    return NULL;
  }

}
