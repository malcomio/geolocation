<?php

namespace Drupal\geolocation_leaflet\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation_leaflet\Plugin\geolocation\MapProvider\Leaflet;
use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

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
 * @property \Drupal\geolocation_leaflet\Plugin\geolocation\MapProvider\Leaflet $mapProvider
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

    if (!empty($settings['common_map'])) {
      $elements['#maptype'] = 'leaflet';
      $unique_id = $elements['#uniqueid'];

      $elements['#attached'] = array_merge_recursive($elements['#attached'], $this->mapProvider->attachments($leaflet_settings, $unique_id));
    }
    else {
      foreach (Element::children($elements) as $delta => $element) {
        $elements[$delta]['#maptype'] = 'leaflet';
        $unique_id = $elements[$delta]['#uniqueid'];
        $elements['#attached'] = array_merge_recursive($elements['#attached'], $this->mapProvider->attachments($leaflet_settings, $unique_id));
      }
    }

    return $elements;
  }

}
