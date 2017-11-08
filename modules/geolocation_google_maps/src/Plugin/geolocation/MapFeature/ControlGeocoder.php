<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Geocoding control element.
 *
 * @MapFeature(
 *   id = "control_geocoder",
 *   name = @Translation("Map Control - Geocoder"),
 *   description = @Translation("Add address search with geocoding functionality map."),
 *   type = "google_maps",
 * )
 */
class ControlGeocoder extends ControlMapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return array_replace_recursive(
      parent::getDefaultSettings(),
      [
        'geocoder' => 'google_geocoding_api',
        'settings' => [],
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('Geocoder enabled');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $form = parent::getSettingsForm($settings, $parents);

    $settings = array_replace_recursive(
      self::getDefaultSettings(),
      $settings
    );

    /** @var \Drupal\geolocation\GeocoderManager $geocoder_manager */
    $geocoder_manager = \Drupal::service('plugin.manager.geolocation.geocoder');
    $geocoder_definitions = $geocoder_manager->getBoundaryCapableGeocoders();

    if ($geocoder_definitions) {
      $geocoder_options = [];
      foreach ($geocoder_definitions as $id => $definition) {
        $geocoder_options[$id] = $definition['name'];
      }

      $form['geocoder'] = [
        '#type' => 'select',
        '#options' => $geocoder_options,
        '#title' => $this->t('Geocoder plugin'),
        '#default_value' => $settings['geocoder'],
        '#ajax' => [
          'callback' => [get_class($geocoder_manager), 'addGeocoderSettingsFormAjax'],
          'wrapper' => 'geocoder-plugin-settings',
          'effect' => 'fade',
        ],
      ];

      if (!empty($settings['geocoder'])) {
        $geocoder_plugin = $geocoder_manager->getGeocoder(
            $settings['geocoder'],
            $settings['settings']
          );
      }
      elseif (current(array_keys($geocoder_options))) {
        $geocoder_plugin = $geocoder_manager->getGeocoder(current(array_keys($geocoder_options)));
      }

      if (!empty($geocoder_plugin)) {
        $geocoder_settings_form = $geocoder_plugin->getOptionsForm();
        if ($geocoder_settings_form) {
          $form['settings'] = $geocoder_settings_form;
        }
      }

      if (empty($form['settings'])) {
        $form['settings'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t("No settings available."),
        ];
      }

      $form['settings'] = array_replace_recursive($form['settings'], [
        '#flatten' => TRUE,
        '#prefix' => '<div id="geocoder-plugin-settings">',
        '#suffix' => '</div>',
      ]);

      $form['#element_validate'][] = [$this, 'validateSettingsForm'];
    }

    return $form;
  }

  /**
   * Validate form.
   *
   * @param array $element
   *   Form element to check.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   * @param array $form
   *   Current form.
   */
  public function validateSettingsForm(array $element, FormStateInterface $form_state, array $form) {
    $values = $form_state->getValues();
    $parents = [];
    if (!empty($element['#parents'])) {
      $parents = $element['#parents'];
      $values = NestedArray::getValue($values, $parents);
    }

    if ($values['geocoder']) {
      /** @var \Drupal\geolocation\GeocoderInterface $geocoder_plugin */
      $geocoder_plugin = \Drupal::service('plugin.manager.geolocation.geocoder')
        ->getGeocoder(
          $values['geocoder'],
          $values['settings']
        );

      if (!empty($geocoder_plugin)) {
        $geocoder_plugin->formvalidateInput($form_state);
      }
      else {
        $form_state->setErrorByName(implode('][', $parents), $this->t('invalid geocoder.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $settings = $this->getSettings($settings);

    /** @var \Drupal\geolocation\GeocoderInterface $geocoder_plugin */
    $geocoder_plugin = \Drupal::service('plugin.manager.geolocation.geocoder')
      ->getGeocoder(
        $settings['geocoder'],
        $settings['settings']
      );

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      $geocoder_plugin->attachments($map_id)
    );

    $render_array['#controls'][$this->pluginId]['#type'] = 'container';

    /** @var \Drupal\geolocation\GeocoderInterface $geocoder_plugin */
    $geocoder_plugin = \Drupal::service('plugin.manager.geolocation.geocoder')
      ->getGeocoder(
        $settings['geocoder'],
        $settings['settings']
      );
    $geocoder_plugin->formAttachGeocoder($render_array['#controls'][$this->pluginId], $render_array['#id']);

    return $render_array;
  }

}
