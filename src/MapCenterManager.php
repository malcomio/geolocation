<?php

namespace Drupal\geolocation;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\SortArray;

/**
 * Search plugin manager.
 */
class MapCenterManager extends DefaultPluginManager {

  /**
   * Constructs an MapCenterManager object.
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
    parent::__construct('Plugin/geolocation/MapCenter', $namespaces, $module_handler, 'Drupal\geolocation\MapCenterInterface', 'Drupal\geolocation\Annotation\MapCenter');
    $this->alterInfo('geolocation_mapcenter_info');
    $this->setCacheBackend($cache_backend, 'geolocation_mapcenter');
  }

  /**
   * Return MapCenter by ID.
   *
   * @param string $id
   *   MapCenter ID.
   * @param array $configuration
   *   Configuration.
   *
   * @return \Drupal\geolocation\MapCenterInterface|false
   *   MapCenter instance.
   */
  public function getMapCenter($id, array $configuration = []) {
    $definitions = $this->getDefinitions();
    if (empty($definitions[$id])) {
      return FALSE;
    }
    try {
      /** @var \Drupal\geolocation\MapCenterInterface $instance */
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
  public function getCenterOptionsForm(array $settings, array $context = []) {
    $form = [
      '#type' => 'table',
      '#prefix' => t('<h3>Centre options</h3>Please note: Each option will, if it can be applied, supersede any following option.'),
      '#header' => [
        [
          'data' => t('Enable'),
          'colspan' => 2,
        ],
        t('Option'),
        t('Settings'),
        t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'geolocation-centre-option-weight',
        ],
      ],
    ];

    foreach ($this->getDefinitions() as $map_center_id => $map_center_definition) {
      /** @var \Drupal\geolocation\MapCenterInterface $map_center */
      $map_center = $this->createInstance($map_center_id);
      foreach ($map_center->getAvailableMapCenterOptions($context) as $option_id => $label) {
        $option_enable_id = HTML::getUniqueId($option_id . '_enabled');
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
          'map_center_id' => [
            '#type' => 'value',
            '#value' => $map_center_id,
          ],
          'option' => [
            '#markup' => $label,
          ],
          'settings' => [
            '#markup' => '',
          ],
          'weight' => [
            '#type' => 'weight',
            '#title' => t('Weight for @option', ['@option' => $label]),
            '#title_display' => 'invisible',
            '#size' => 4,
            '#default_value' => $weight,
            '#attributes' => ['class' => ['geolocation-centre-option-weight']],
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

  /**
   * Get center values for map element.
   *
   * @param array $settings
   *   Center option settings.
   *
   * @return array
   *   Centre value.
   */
  public function getCenterValue(array $settings) {
    $centre = [];

    /*
     * Centre handling.
     */
    foreach ($settings as $option_id => $option) {
      // Ignore if not enabled.
      if (empty($option['enable'])) {
        continue;
      }

      // Compatibility to v1.
      if (empty($option['map_center_id'])) {
        $option['map_center_id'] = $option_id;
      }

      // Failsafe.
      if (!$this->hasDefinition($option['map_center_id'])) {
        $option['map_center_id'] = $option_id = 'fit_bounds';
      }

      /** @var \Drupal\geolocation\MapCenterInterface $map_center_plugin */
      $map_center_plugin = $this->createInstance($option['map_center_id']);
      $current_map_center = $map_center_plugin->getMapCenter($option_id, empty($option['settings']) ? [] : $option['settings'], ['views_style' => $this]);

      if (
        isset($current_map_center['behavior'])
        && !isset($centre['behavior'])
      ) {
        $centre['behavior'] = $current_map_center['behavior'];
      }

      if (
        (
          !isset($centre['lat'])
          && !isset($centre['lng'])
        )
        ||
        (
          !isset($centre['lat_north_east'])
          && !isset($centre['lng_north_east'])
          && !isset($centre['lat_south_west'])
          && !isset($centre['lng_south_west'])
        )
      ) {
        if (
          isset($current_map_center['lat'])
          && isset($current_map_center['lng'])
        ) {
          $centre['lat'] = $current_map_center['lat'];
          $centre['lng'] = $current_map_center['lng'];
        }
        // Break if center bounds are already set.
        elseif (
          isset($centre['lat_north_east'])
          && isset($centre['lng_north_east'])
          && isset($centre['lat_south_west'])
          && isset($centre['lng_south_west'])
        ) {
          $centre['lat_north_east'] = $current_map_center['lat_north_east'];
          $centre['lng_north_east'] = $current_map_center['lng_north_east'];
          $centre['lat_south_west'] = $current_map_center['lat_south_west'];
          $centre['lng_south_west'] = $current_map_center['lng_south_west'];
        }
      }
    }

    if (empty($centre)) {
      $centre = ['lat' => 0, 'lng' => 0];
    }

    return $centre;
  }

}
