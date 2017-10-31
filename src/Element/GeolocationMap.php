<?php

namespace Drupal\geolocation\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\BubbleableMetadata;

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
 *   '#id' => 'thisisanid',
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
      '#id' => NULL,
      '#controls' => NULL,
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

    if (empty($render_array['#id'])) {
      $id = uniqid();
      $render_array['#id'] = $id;
    }
    else {
      $id = $render_array['#id'];
    }

    if (empty($render_array['#maptype'])) {
      if (\Drupal::moduleHandler()->moduleExists('geolocation_google_maps')) {
        $render_array['#maptype'] = 'google_maps';
      }
    }

    $map_provider = $this->mapProviderManager->getMapProvider($render_array['#maptype']);

    if (
      !empty($render_array['#settings'])
      && is_array($render_array['#settings'])
    ) {
      $map_settings = $render_array['#settings'];
    }
    else {
      $map_settings = [];
    }

    $new_attached = $map_provider->attachments($map_settings, $id);

    if (empty($render_array['#attached'])) {
      $render_array['#attached'] = $new_attached;
    }
    else {
      $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
        $render_array['#attached'],
        $new_attached
      );
    }

    array_unshift($render_array['#attached']['library'], 'geolocation/geolocation.map');

    foreach (Element::children($render_array) as $child) {
      $render_array['#children'][] = $render_array[$child];
    }

    $render_array = $map_provider->alterRenderArray($render_array, $map_settings, $id);

    return $render_array;
  }

}
