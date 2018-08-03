<?php

namespace Drupal\geolocation\Plugin\geolocation\Location;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\geolocation\LocationInterface;
use Drupal\geolocation\LocationBase;

/**
 * Derive center from proximity argument.
 *
 * @Location(
 *   id = "views_proximity_argument",
 *   name = @Translation("Proximity argument"),
 *   description = @Translation("Set map center from proximity argument."),
 * )
 */
class ViewsProximityArgument extends LocationBase implements LocationInterface {

  protected $viewsArgumentPluginId = 'geolocation_argument_argument';

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
      $arguments = $views_style->displayHandler->getOption('arguments');
      foreach ($arguments as $argument_id => $argument) {
        if ($argument['plugin_id'] == $this->viewsArgumentPluginId) {
          /** @var \Drupal\views\Plugin\views\argument\ArgumentPluginBase $argument_handler */
          $argument_handler = $views_style->displayHandler->getHandler('argument', $argument_id);

          $options[$argument_id] = $argument_handler->adminLabel();
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

    /** @var \Drupal\geolocation\Plugin\views\argument\ProximityArgument $handler */
    $handler = $views_style->displayHandler->getHandler('argument', $location_option_id);
    if ($values = $handler->getParsedReferenceLocation()) {
      if (isset($values['lat']) && isset($values['lng'])) {
        return [
          'lat' => $values['lat'],
          'lng' => $values['lng'],
        ];
      }
    }

    return parent::getCoordinates($location_option_id, $location_option_settings, $context);
  }

}
