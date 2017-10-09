<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldWidget;

use Drupal\geolocation\Plugin\Field\FieldWidget\GeolocationMapWidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;

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
    $settings += GoogleMaps::getDefaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);

    $settings = $this->getSettings();

    if (empty($settings[$this->mapProviderSettingsFormId])) {
      $settings[$this->mapProviderSettingsFormId] = [];
    }

    $google_map_settings = $this->mapProvider->getSettings($settings[$this->mapProviderSettingsFormId]);

    $element['map_container']['#attached']['library'][] = 'geolocation_google_maps/geolocation.widgets.google';
    $unique_id = $element['map_container']['canvas']['#uniqueid'];

    $element['map_container']['canvas']['#attached'] = array_merge_recursive(
      $element['map_container']['canvas']['#attached'],
      $this->mapProvider->attachments($google_map_settings, $unique_id)
    );

    return $element;
  }

}
