<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\Field\FieldWidget\GeolocationMapWidgetBase;

/**
 * Plugin implementation of the 'geolocation_googlegeocoder' widget.
 *
 * @FieldWidget(
 *   id = "geolocation_googlegeocoder",
 *   label = @Translation("Geolocation Google Maps API - Geocoding and Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GeolocationGoogleWidget extends GeolocationMapWidgetBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'google_maps';

  /**
   * {@inheritdoc}
   */
  protected $mapProviderSettingsFormId = 'google_map_settings';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings['google_map_settings']['map_features']['control_geocoder'] = [
      'enabled' => TRUE,
      'settings' => [
        'weight' => -100,
      ],
    ];
    $settings['google_map_settings']['map_features']['control_recenter']['enabled'] = TRUE;
    $settings['google_map_settings']['map_features']['control_locate']['enabled'] = TRUE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);

    $element['#attributes']['class'][] = 'geolocation-google-map-widget';

    $settings = $this->getSettings();

    if (empty($settings[$this->mapProviderSettingsFormId])) {
      $settings[$this->mapProviderSettingsFormId] = [];
    }

    $google_map_settings = $this->mapProvider->getSettings($settings[$this->mapProviderSettingsFormId]);

    $element['map_container']['map']['#attached']['library'][] = 'geolocation_google_maps/widgets.google';

    $element['map_container']['map']['#settings'] = $google_map_settings;

    return $element;
  }

}
