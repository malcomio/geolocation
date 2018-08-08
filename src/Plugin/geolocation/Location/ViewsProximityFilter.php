<?php

namespace Drupal\geolocation\Plugin\geolocation\Location;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\geolocation\LocationInterface;
use Drupal\geolocation\LocationBase;

/**
 * Derive center from proximity filter.
 *
 * @Location(
 *   id = "views_proximity_filter",
 *   name = @Translation("Proximity filter"),
 *   description = @Translation("Set map center from proximity filter."),
 * )
 */
class ViewsProximityFilter extends LocationBase implements LocationInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvailableLocationOptions(array $context) {
    $options = [];

    if (
      !empty($context['views_style'])
      && is_a($context['views_style'], StylePluginBase::class)
    ) {
      /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
      $views_style = $context['views_style'];
      $filters = $views_style->displayHandler->getOption('filters');
      foreach ($filters as $filter_id => $filter) {
        if ($filter['plugin_id'] == 'geolocation_filter_proximity') {
          /** @var \Drupal\views\Plugin\views\filter\FilterPluginBase $filter_handler */
          $filter_handler = $views_style->displayHandler->getHandler('filter', $filter_id);

          $options[$filter_id] = $filter_handler->adminLabel();
        }
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getCoordinates($location_option_id, array $location_option_settings, array $context = []) {
    if (
      empty($context['views_style'])
      || !is_a($context['views_style'], StylePluginBase::class)
    ) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
    $views_style = $context['views_style'];

    /** @var \Drupal\geolocation\Plugin\views\filter\ProximityFilter $handler */
    $handler = $views_style->displayHandler->getHandler('filter', $location_option_id);
    if (isset($handler->value['lat']) && isset($handler->value['lng'])) {
      return [
        'lat' => (float) $handler->getLatitudeValue(),
        'lng' => (float) $handler->getLongitudeValue(),
      ];
    }

    return parent::getCoordinates($location_option_id, $location_option_settings, $context);
  }

}
