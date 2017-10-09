<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "control_geocoder",
 *   name = @Translation("Control Geocoder"),
 *   description = @Translation("Add address search with geocoding functionality map."),
 *   type = "google_maps",
 * )
 */
class ControlGeocoder extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'geocoder' => '',
    ];
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
    $settings = $this->getSettings($settings);
    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Various <a href=":url">examples</a> are available.', [':url' => 'https://developers.google.com/maps/documentation/javascript/marker-clustering']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $maps_id) {
    return [
      'library' => [
        'geolocation_google_maps/geolocation.control_recenter',
      ],
      'drupalSettings' => [
        'geolocation' => [
          'maps' => [
            $maps_id => [
              'control_recenter' => [
                'enable' => TRUE,
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
