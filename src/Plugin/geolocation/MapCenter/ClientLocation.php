<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Fixed coordinates map center.
 *
 * @MapCenter(
 *   id = "client_location",
 *   name = @Translation("Client location"),
 *   description = @Translation("Automatically fit map to client location. Might not be available."),
 * )
 */
class ClientLocation extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $map, $center_option_id, array $center_option_settings, array $context = []) {
    $map = parent::alterMap($map, $center_option_id, $center_option_settings, $context);
    $map['#attached'] = array_merge_recursive($map['#attached'], [
      'library' => [
        'geolocation/map_center.client_location',
      ],
    ]);

    return $map;
  }

}
