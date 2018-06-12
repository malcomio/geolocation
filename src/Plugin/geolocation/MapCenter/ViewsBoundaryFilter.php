<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Derive center from boundary filter.
 *
 * @MapCenter(
 *   id = "views_boundary_filter",
 *   name = @Translation("Boundary filter"),
 *   description = @Translation("Fit map to boundary filter."),
 * )
 */
class ViewsBoundaryFilter extends ViewsFilterCenterBase {

  protected $viewsFilterPluginId = 'geolocation_filter_boundary';

  /**
   * {@inheritdoc}
   */
  public function getAvailableMapCenterOptions(array $context) {
    $options = parent::getAvailableMapCenterOptions($context);

    // Preserve compatibility to v1.
    $prefixed_options = [];
    foreach ($options as $option_id => $option) {
      $prefixed_options['boundary_filter_' . $option_id] = $option;
    }

    return $prefixed_options;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    $center_definition = parent::getMapCenter($center_option_id, $center_option_settings, $context);

    if (
      empty($context['views_style'])
      || !is_a($context['views_style'], StylePluginBase::class)
    ) {
      return $center_definition;
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
      $center_definition = array_replace_recursive(
        $center_definition,
        [
          'lat_north_east' => (float) $handler->value['lat_north_east'],
          'lng_north_east' => (float) $handler->value['lng_north_east'],
          'lat_south_west' => (float) $handler->value['lat_south_west'],
          'lng_south_west' => (float) $handler->value['lng_south_west'],
          'behavior' => 'fitboundaries',
        ]
      );
    }

    return $center_definition;
  }

}
