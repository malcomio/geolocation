<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Fixed coordinates map center.
 *
 * ID for compatibility with v1.
 *
 * @MapCenter(
 *   id = "fit_bounds",
 *   name = @Translation("Fit locations"),
 *   description = @Translation("Automatically fit map to displayed locations."),
 * )
 */
class FitLocations extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    return array_replace_recursive(
      parent::getMapCenter($center_option_id, $center_option_settings, $context),
      ['behavior' => 'fitlocations']
    );
  }

}
