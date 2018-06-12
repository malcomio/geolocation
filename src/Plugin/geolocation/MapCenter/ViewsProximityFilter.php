<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Derive center from proximity filter.
 *
 * @MapCenter(
 *   id = "views_proximity_filter",
 *   name = @Translation("Proximity filter"),
 *   description = @Translation("Set map center from proximity filter."),
 * )
 */
class ViewsProximityFilter extends ViewsFilterCenterBase {

  protected $viewsFilterPluginId = 'geolocation_filter_proximity';

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
    if (isset($handler->value['lat']) && isset($handler->value['lng'])) {
      $center_definition = array_replace_recursive(
        $center_definition,
        [
          'lat' => (float) $handler->getLatitudeValue(),
          'lng' => (float) $handler->getLongitudeValue(),
          'behavior' => 'preset',
        ]
      );
    }

    return $center_definition;
  }

}
