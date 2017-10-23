<?php

namespace Drupal\geolocation\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;

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
 *   '#centre' => [],
 *   '#uniqueid' => 'thisisanid',
 * ];
 * @endcode
 *
 * @FormElement("geolocation_map")
 */
class GeolocationMap extends FormElement {

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
    $class = get_class($this);

    $info = [
      '#process' => [
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
        [$this, 'preRenderMap'],
      ],
      '#maptype' => NULL,
      '#centre' => NULL,
      '#uniqueid' => NULL,
      '#controls' => NULL,
      '#children' => NULL,
    ];

    return $info;
  }

  /**
   * Map element.
   *
   * @param array $render_array
   *   Element.
   *
   * @return array
   *   Renderable map.
   */
  public function preRenderMap(array $render_array) {
    $render_array['#theme'] = 'geolocation_map_wrapper';

    if (empty($render_array['#uniqueid'])) {
      $unique_id = uniqid();
      $render_array['#uniqueid'] = $unique_id;
    }
    else {
      $unique_id = $render_array['#uniqueid'];
    }

    $render_array['#attached']['drupalSettings']['geolocation']['maps'][$unique_id] = [
      'id' => $unique_id,
    ];

    if (empty($render_array['#maptype'])) {
      if (\Drupal::moduleHandler()->moduleExists('geolocation_google_maps')) {
        $render_array['#maptype'] = 'google_maps';
      }
    }

    if (empty($render_array['#settings'][$render_array['#maptype'] . '_settings'])) {
      $render_array['#settings'][$render_array['#maptype'] . '_settings'] = [];
    }

    $render_array['#attached'] = array_merge_recursive(
      $render_array['#attached'],
      $this->mapProviderManager->getMapProvider($render_array['#maptype'])->attachments($render_array['#settings'][$render_array['#maptype'] . '_settings'], $unique_id)
    );

    $render_array['#attached']['library'][] = 'geolocation/geolocation.map';

    foreach (Element::children($render_array) as $child) {
      $render_array['#children'][] = $render_array[$child];
    }

    return $render_array;
  }

}
