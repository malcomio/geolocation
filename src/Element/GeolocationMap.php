<?php

namespace Drupal\geolocation\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element to display a geolocation map.
 *
 * Usage example:
 * @code
 * $form['map'] = [
 *   '#type' => 'geolocation_map',
 *   '#prefix' => $this->t('Geolocation Map Render Element'),
 *   '#description' => $this->t('Render element type "geolocation_map"'),
 *   '#maptype' => 'leaflet,
 *   '#locations' => [],
 *   '#centre' => [],
 *   '#uniqueid' => 'thisisanid',
 * ];
 * @endcode
 *
 * @FormElement("geolocation_map")
 */
class GeolocationMap extends RenderElement {

  /**
   * Map Provider.
   *
   * @var \Drupal\geolocation\MapProviderManager
   */
  protected $mapProviderManager = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mapProviderManager = \Drupal::service('plugin.manager.geolocation.mapprovider');
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#pre_render' => [
        [$this, 'preRenderMap'],
      ],
      '#maptype' => NULL,
      '#locations' => NULL,
      '#centre' => NULL,
      '#uniqueid' => NULL,
    ];
  }

  /**
   * Map element.
   *
   * @param array $element
   *   Element.
   *
   * @return array
   *   Renderable map.
   */
  public function preRenderMap(array $element) {
    $render_array = [
      '#theme' => 'geolocation_map_wrapper',
      '#uniqueid' => $element['#uniqueid'],
    ];

    if (empty($element['#maptype'])) {
      $element['#maptype'] = 'google_maps';
    }

    $render_array['#maptype'] = $element['#maptype'];

    $render_array['#attached']['library'][] = 'geolocation/geolocation.map';

    if (!empty($element['#prefix'])) {
      $render_array['#prefix'] = $element['#prefix'];
    }

    if (!empty($element['#suffix'])) {
      $render_array['#suffix'] = $element['#suffix'];
    }

    if (!empty($element['#locations'])) {
      $render_array['#locations'] = $element['#locations'];
    }

    if (!empty($element['#centre'])) {
      $render_array['#centre'] = $element['#centre'];
    }

    $children = Element::children($element, TRUE);
    if ($children) {
      $render_array['#children'] = $children;
    }

    return $render_array;
  }

}
