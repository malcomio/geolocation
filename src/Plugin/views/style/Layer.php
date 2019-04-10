<?php

namespace Drupal\geolocation\Plugin\views\style;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "geolocation_layer",
 *   title = @Translation("Geolocation Layer"),
 *   help = @Translation("Display geolocations on a layer."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 */
class Layer extends GeolocationStyleBase {

  /**
   * {@inheritdoc}
   */
  public function render() {

    if (empty($this->options['geolocation_field'])) {
      \Drupal::messenger()->addMessage('The geolocation common map ' . $this->view->id() . ' views style was called without a geolocation field defined in the views style settings.', 'error');
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

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $this->displayHandler->display['id'],
        'class' => [
          'geolocation-layer',
        ],
      ],
    ];

    /*
     * Add locations to output.
     */
    foreach ($this->view->result as $row_number => $row) {
      foreach ($this->getLocationsFromRow($row) as $location) {
        $build['locations'][] = $location;
      }
    }

    return $build;
  }

}
