<?php

namespace Drupal\geolocation_leaflet\Plugin\views\style;

use Drupal\geolocation\Plugin\views\style\CommonMapBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "geolocation_leaflet",
 *   title = @Translation("Geolocation Leaflet - CommonMap"),
 *   help = @Translation("Display geolocations on a common map."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 */
class LeafletCommonMap extends CommonMapBase {

  /**
   * {@inheritdoc}
   */
  public function render() {

    $build = parent::render();
    $build['#maptype'] = 'leaflet';

    $build['#attached'] = array_merge_recursive(
      empty($build['#attached']) ? [] : $build['#attached'],
      $this->mapProviderManager
        ->createInstance('leaflet')
        ->attachments(empty($this->options['leaflet_settings']) ? [] : $this->options['leaflet_settings'], $this->mapId),
      [
        'library' => [
          'geolocation_leaflet/geolocation.leaflet',
        ],
      ]
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    parent::buildOptionsForm($form, $form_state);

    $form['leaflet_settings'] = $this
      ->mapProviderManager
      ->createInstance('leaflet')
      ->getSettingsForm(empty($this->options['leaflet_settings']) ? [] : $this->options['leaflet_settings'], ['style_options', 'leaflet_settings']);
  }

}
