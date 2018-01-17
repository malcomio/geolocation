<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Fixed coordinates map center.
 *
 * @MapCenter(
 *   id = "client_location",
 *   name = @Translation("Client locations"),
 *   description = @Translation("Automatically fit map to client location. Might not be available."),
 * )
 */
class ClientLocation extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    return array_replace_recursive(
      parent::getMapCenter($center_option_id, $center_option_settings, $context),
      ['behavior' => 'client_location']
    );
  }

}
