<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Derive center from boundary filter.
 *
 * @MapCenter(
 *   id = "views_boundary_filter",
 *   name = @Translation("Boundary filter"),
 *   description = @Translation("Fit map to boundary filter."),
 * )
 */
class ViewsBoundaryFilter extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvailableMapCenterOptions(array $context) {
    $options = [];

    if (
      !empty($context['views_style'])
      && is_a($context['views_style'], StylePluginBase::class)
    ) {
      /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
      $views_style = $context['views_style'];
      $filters = $views_style->displayHandler->getOption('filters');
      foreach ($filters as $filter_id => $filter) {
        if ($filter['plugin_id'] == 'geolocation_filter_boundary') {
          /** @var \Drupal\views\Plugin\views\filter\FilterPluginBase $filter_handler */
          $filter_handler = $views_style->displayHandler->getHandler('filter', $filter_id);

          // Preserve compatibility to v1.
          $options['boundary_filter_' . $filter_id] = $filter_handler->adminLabel();
        }
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $map, $center_option_id, array $center_option_settings, array $context = []) {
    $map = parent::alterMap($map, $center_option_id, $center_option_settings, $context);

    if (
      empty($context['views_style'])
      || !is_a($context['views_style'], StylePluginBase::class)
    ) {
      return $map;
    }

    /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
    $views_style = $context['views_style'];

    /** @var \Drupal\geolocation\Plugin\views\filter\ProximityFilter $handler */
    $handler = $views_style->displayHandler->getHandler('filter', $center_option_id);
    if (
      isset($handler->value['lat_north_east'])
      && $handler->value['lat_north_east'] !== ""
      && isset($handler->value['lng_north_east'])
      && $handler->value['lng_north_east'] !== ""
      && isset($handler->value['lat_south_west'])
      && $handler->value['lat_south_west'] !== ""
      && isset($handler->value['lng_south_west'])
      && $handler->value['lng_south_west'] !== ""
    ) {
      $map['#attached'] = array_merge_recursive($map['#attached'], [
        'library' => [
          'geolocation/map_center.fitboundary',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map['#id'] => [
                'map_center' => [
                  'views_boundary_filter' => [
                    'latNorthEast' => (float) $handler->value['lat_north_east'],
                    'lngNorthEast' => (float) $handler->value['lng_north_east'],
                    'latSouthWest' => (float) $handler->value['lat_south_west'],
                    'lngSouthWest' => (float) $handler->value['lng_south_west'],
                  ],
                ],
              ],
            ],
          ],
        ],
      ]);
    }

    return $map;
  }

}
