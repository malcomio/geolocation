<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\SortArray;

/**
 * Search plugin manager.
 */
class LocationManager extends DefaultPluginManager {

  /**
   * Constructs an LocationManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/geolocation/Location', $namespaces, $module_handler, 'Drupal\geolocation\LocationInterface', 'Drupal\geolocation\Annotation\Location');
    $this->alterInfo('geolocation_location_info');
    $this->setCacheBackend($cache_backend, 'geolocation_location');
  }

  /**
   * Return Location by ID.
   *
   * @param string $id
   *   Location ID.
   * @param array $configuration
   *   Configuration.
   *
   * @return \Drupal\geolocation\LocationInterface|false
   *   Location instance.
   */
  public function getLocationPlugin($id, array $configuration = []) {
    $definitions = $this->getDefinitions();
    if (empty($definitions[$id])) {
      return FALSE;
    }
    try {
      /** @var \Drupal\geolocation\LocationInterface $instance */
      $instance = $this->createInstance($id, $configuration);
      if ($instance) {
        return $instance;
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Get form render array.
   *
   * @param array $settings
   *   Settings.
   * @param array $context
   *   Optional context.
   *
   * @return array
   *   Form.
   */
  public function getLocationOptionsForm(array $settings, array $context = []) {
    $form = [
      '#type' => 'table',
      '#prefix' => t('<h3>Centre options</h3>Please note: Each option will, if it can be applied, supersede any following option.'),
      '#header' => [
        t('Enable'),
        t('Option'),
        t('Settings'),
        t('Settings'),
      ],
      '#attributes' => ['id' => 'geolocation-centre-options'],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'geolocation-centre-option-weight',
        ],
      ],
    ];

    foreach ($this->getDefinitions() as $map_center_id => $map_center_definition) {
      /** @var \Drupal\geolocation\LocationInterface $mapCenter */
      $map_center = $this->createInstance($map_center_id);
      foreach ($map_center->getAvailableLocationOptions($context) as $option_id => $label) {
        $option_enable_id = uniqid($option_id . '_enabled');
        $weight = isset($settings[$option_id]['weight']) ? $settings[$option_id]['weight'] : 0;
        $form[$option_id] = [
          '#weight' => $weight,
          '#attributes' => [
            'class' => [
              'draggable',
            ],
          ],
          'enable' => [
            '#attributes' => [
              'id' => $option_enable_id,
            ],
            '#type' => 'checkbox',
            '#default_value' => isset($settings[$option_id]['enable']) ? $settings[$option_id]['enable'] : FALSE,
          ],
          'option' => [
            '#markup' => $label,
          ],
          'weight' => [
            '#type' => 'weight',
            '#title' => t('Weight for @option', ['@option' => $label]),
            '#title_display' => 'invisible',
            '#size' => 4,
            '#default_value' => $weight,
            '#attributes' => ['class' => ['geolocation-centre-option-weight']],
          ],
          'map_center_id' => [
            '#type' => 'value',
            '#value' => $map_center_id,
          ],
        ];

        $option_form = $map_center->getSettingsForm(
          $option_id,
          $context,
          empty($settings[$option_id]['settings']) ? [] : $settings[$option_id]['settings']
        );

        if (!empty($option_form)) {
          $option_form['#states'] = [
            'visible' => [
              ':input[id="' . $option_enable_id . '"]' => ['checked' => TRUE],
            ],
          ];
          $option_form['#type'] = 'item';

          $form[$option_id]['settings'] = $option_form;
        }
      }
    }

    uasort($form, [SortArray::class, 'sortByWeightProperty']);

    return $form;
  }

}
