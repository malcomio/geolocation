<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Derive center from boundary filter.
 *
 * @MapCenter(
 *   id = "views_boundary_argument",
 *   name = @Translation("Boundary argument"),
 *   description = @Translation("Fit map to boundary argument."),
 * )
 */
class ViewsBoundaryArgument extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvailableMapCenterOptions(array $context) {
    $options = [];

    if (
      !empty($context['views_style'])
      && is_a($context['views_style'], StylePluginBase::class)
    ) {
      $views_style = $context['views_style'];
      $arguments = $views_style->displayHandler->getOption('arguments');
      foreach ($arguments as $argument_id => $argument) {
        if ($argument['plugin_id'] == 'geolocation_argument_boundary') {
          /** @var \Drupal\views\Plugin\views\argument\ArgumentPluginBase $argument_handler */
          $argument_handler = $views_style->displayHandler->getHandler('argument', $argument_id);

          $options['boundary_argument_' . $argument_id] = $argument_handler->adminLabel();
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

    /** @var \Drupal\geolocation\Plugin\views\argument\BoundaryArgument $handler */
    $handler = $views_style->displayHandler->getHandler('filter', $center_option_id);
    if ($values = $handler->getParsedBoundary()) {

      if (
        isset($values['lat_north_east'])
        && $values['lat_north_east'] !== ""
        && isset($values['lng_north_east'])
        && $values['lng_north_east'] !== ""
        && isset($values['lat_south_west'])
        && $values['lat_south_west'] !== ""
        && isset($values['lng_south_west'])
        && $values['lng_south_west'] !== ""
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
                      'latNorthEast' => (float) $values['lat_north_east'],
                      'lngNorthEast' => (float) $values['lng_north_east'],
                      'latSouthWest' => (float) $values['lat_south_west'],
                      'lngSouthWest' => (float) $values['lng_south_west'],
                    ],
                  ],
                ],
              ],
            ],
          ],
        ]);
      }
    }

    return $map;
  }

}
