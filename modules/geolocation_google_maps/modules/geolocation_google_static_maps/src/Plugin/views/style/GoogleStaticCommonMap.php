<?php

namespace Drupal\geolocation_google_static_maps\Plugin\views\style;

use Drupal\geolocation\Plugin\views\style\CommonMapBase;
use Drupal\Core\Url;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "google_static_common_map",
 *   title = @Translation("Geolocation Google Static Maps - CommonMap"),
 *   help = @Translation("Display geolocations on a static common map."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 *
 * @property \Drupal\geolocation_google_static_maps\Plugin\geolocation\MapProvider\GoogleStaticMaps $mapProvider
 */
class GoogleStaticCommonMap extends CommonMapBase {

  protected $mapProviderId = 'google_static_maps';

  /**
   * {@inheritdoc}
   */
  public function render() {

    if (empty($this->options['geolocation_field'])) {
      \Drupal::logger('geolocation')->error("The geolocation common map ' . $this->view->id() . ' views style was called without a geolocation field defined in the views style settings.");
      // Enable after 8.5 release: \Drupal::messenger()->addMessage("The geolocation common map ' . $this->view->id() . ' views style was called without a geolocation field defined in the views style settings.", 'error');
      return [];
    }

    if (
      !empty($this->options['title_field'])
      && $this->options['title_field'] != 'none'
    ) {
      $this->titleField = $this->options['title_field'];
    }

    if (
      !empty($this->options['icon_field'])
      && $this->options['icon_field'] != 'none'
    ) {
      $this->iconField = $this->options['icon_field'];
    }

    $map_settings = [];
    if (!empty($this->options[$this->mapProviderSettingsFormId])) {
      $map_settings = $this->options[$this->mapProviderSettingsFormId];
    }

    $additional_parameters = [
      'type' => strtolower($map_settings['type']),
      'size' => filter_var($map_settings['width'], FILTER_SANITIZE_NUMBER_INT) . 'x' . filter_var($map_settings['height'], FILTER_SANITIZE_NUMBER_INT),
      'zoom' => $map_settings['zoom'],
      'scale' => (int) $map_settings['scale'],
      'format' => $map_settings['format'],
    ];

    $centre = [];
    /*
     * Centre handling.
     */
    foreach ($this->options['centre'] as $option_id => $option) {
      // Ignore if not enabled.
      if (empty($option['enable'])) {
        continue;
      }

      // Compatibility to v1.
      if (empty($option['map_center_id'])) {
        $option['map_center_id'] = $option_id;
      }

      // Failsafe.
      if (!$this->mapCenterManager->hasDefinition($option['map_center_id'])) {
        continue;
      }

      /** @var \Drupal\geolocation\MapCenterInterface $map_center_plugin */
      $map_center_plugin = $this->mapCenterManager->createInstance($option['map_center_id']);
      $current_map_center = $map_center_plugin->getMapCenter($option_id, empty($option['settings']) ? [] : $option['settings'], ['views_style' => $this]);

      if (
        isset($current_map_center['behavior'])
        && !isset($centre['behavior'])
      ) {
        $centre['behavior'] = $current_map_center['behavior'];
      }

      if (
        (!isset($centre['lat']) && !isset($centre['lng']))
        && (isset($current_map_center['lat']) && isset($current_map_center['lng']))
      ) {
        $centre['lat'] = $current_map_center['lat'];
        $centre['lng'] = $current_map_center['lng'];
      }
    }

    if ($centre['behavior'] !== 'fitlocations') {
      if (isset($centre['lat']) && isset($centre['lng'])) {
        $additional_parameters['center'] = $centre['lat'] . ',' . $centre['lng'];
      }
    }

    $static_map_url = $this->mapProvider->getGoogleMapsApiUrl($additional_parameters);

    $this->renderFields($this->view->result);
    foreach ($this->view->result as $row_number => $row) {
      foreach ($this->getLocationsFromRow($row) as $location) {
        $marker_string = '&markers=';
        if (!empty($location['#icon'])) {
          $marker_string .= 'icon:' . Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString() . $location['#icon'] . urlencode('|');
        }
        $marker_string .= $location['#position']['lat'] . ',' . $location['#position']['lng'];
        $static_map_url .= $marker_string;
      }
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => $static_map_url,
      ],
    ];
  }

}
