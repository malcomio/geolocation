<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;

/**
 * Fixed coordinates map center.
 *
 * @MapCenter(
 *   id = "freeogeoip",
 *   name = @Translation("freegoip.net Service"),
 *   description = @Translation("See http://freegeoip.net website. Limited to 15000 requests per hour."),
 * )
 */
class FreeGeoIp extends MapCenterBase implements MapCenterInterface {

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    $ip = \Drupal::request()->getClientIp();
    if (empty($ip)) {
      return [];
    }

    $json = file_get_contents("http://freegeoip.net/json/" . $ip);
    if (empty($json)) {
      return [];
    }

    $result = json_decode($json, TRUE);
    if (
      empty($result)
      || empty($result['latitude'])
      || empty($result['longitude'])
    ) {
      return [];
    }

    return [
      'lat' => (float) $result['latitude'],
      'lng' => (float) $result['longitude'],
      'behavior' => 'preset',
    ];
  }

}
