<?php

namespace Drupal\geolocation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\geolocation\MapProvider\Leaflet;

/**
 * Leaflet map formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_leaflet_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Leaflet - Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 *
 * @property \Drupal\geolocation\Plugin\geolocation\MapProvider\Leaflet $mapProvider
 */
class GeolocationLeafletMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'leaflet';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings += Leaflet::getDefaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $form = parent::settingsForm($form, $form_state);

    $form += $this->mapProvider->getSettingsForm($settings, 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary = parent::settingsSummary();
    $summary = array_merge($summary, $this->mapProvider->getSettingsSummary($settings));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    $settings = $this->getSettings();

    $leaflet_settings = $this->mapProvider->getSettings($settings);

    $elements['#attached']['library'] = array_merge($elements['#attached']['library'], $this->mapProvider->getLibraries());

    if (!empty($settings['common_map'])) {
      $elements['#maptype'] = 'leaflet';
      $unique_id = $elements['#uniqueid'];
      $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] = $leaflet_settings;

    }
    else {
      foreach (Element::children($elements) as $delta => $element) {
        $elements[$delta]['#maptype'] = 'leaflet';
        $unique_id = $elements[$delta]['#uniqueid'];
        $elements['#attached']['drupalSettings']['geolocation']['maps'][$unique_id]['settings'] = $leaflet_settings;
      }
    }

    return $elements;
  }

}
