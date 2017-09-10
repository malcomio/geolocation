<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\geolocation\CommonGoogleMapDrawingTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;
use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_latlng' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Google Maps API - Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 *
 * @property \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps $mapProvider
 */
class GeolocationGoogleMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'google_maps';

  use CommonGoogleMapDrawingTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings += GoogleMaps::getDefaultSettings();
    $settings += self::getCommonGoogleMapDrawingDefaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $form = parent::settingsForm($form, $form_state);

    $form += $this->mapProvider->getSettingsForm($settings, 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][');

    $form['use_overridden_map_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom map settings if provided'),
      '#description' => $this->t('The Geolocation GoogleGeocoder widget optionally allows to define custom map settings to use here.'),
      '#default_value' => $settings['use_overridden_map_settings'],
    ];

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (
      $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
      || $cardinality > 1
    ) {
      $form_prefix = 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][';

      $form += $this->getCommonGoogleMapDrawingSettingsForm($settings, $form_prefix);

      $form['common_google_map_drawing_settings']['#states'] = [
        'visible' => [
          'input[name="' . $form_prefix . 'common_map]"]' => ['checked' => TRUE],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary = parent::settingsSummary();
    $summary = array_merge($summary, $this->mapProvider->getSettingsSummary($settings));
    $summary = array_merge($summary, $this->getCommonGoogleMapDrawingSettingsSummary($settings));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    $settings = $this->getSettings();

    $google_map_settings = $this->mapProvider->getSettings($settings);

    if (
      $settings['use_overridden_map_settings']
      && !empty($items->get(0)->getValue()['data']['google_map_settings'])
      && is_array($items->get(0)->getValue()['data']['google_map_settings'])
    ) {
      $google_map_settings = $this->mapProvider->getSettings($items->get(0)->getValue()['data']);
    }

    $elements['#attached']['library'] = array_merge($elements['#attached']['library'], $this->mapProvider->attachments($google_map_settings));

    if (!empty($settings['common_map'])) {
      $unique_id = $elements['#uniqueid'];
      $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] = $google_map_settings;
    }
    else {
      foreach (Element::children($elements) as $delta => $element) {
        $unique_id = $elements[$delta]['#uniqueid'];
        $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] = $google_map_settings;
      }
    }

    if (
      !empty($settings['common_google_map_drawing_settings']['polyline'])
      || !empty($settings['common_google_map_drawing_settings']['polygon'])
    ) {
      $elements['#attached']['library'][] = 'geolocation/geolocation.commonmap.draw';

      if (!empty($settings['common_map'])) {
        $unique_id = $elements['#uniqueid'];
        $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] += $this->getCommonGoogleMapDrawingSettings($settings);
      }
      else {
        foreach (Element::children($elements) as $delta => $element) {
          $unique_id = $elements[$delta]['#uniqueid'];
          $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] += $this->getCommonGoogleMapDrawingSettings($settings);
        }
      }
    }

    return $elements;
  }

}
