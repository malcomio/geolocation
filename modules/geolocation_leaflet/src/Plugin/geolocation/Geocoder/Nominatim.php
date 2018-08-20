<?php

namespace Drupal\geolocation_leaflet\Plugin\geolocation\Geocoder;

use Drupal\geolocation\GeocoderBase;
use Drupal\geolocation\GeocoderInterface;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Url;

/**
 * Provides the Google Geocoding API.
 *
 * @Geocoder(
 *   id = "nominatim",
 *   name = @Translation("Nominatim"),
 *   description = @Translation("See https://wiki.openstreetmap.org/wiki/Nominatim for details."),
 *   locationCapable = true,
 *   boundaryCapable = true,
 * )
 */
class Nominatim extends GeocoderBase implements GeocoderInterface {

  protected $geocoderId = 'nominatim';

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {
    if (empty($address)) {
      return FALSE;
    }

    $url = Url::fromUri('https://nominatim.openstreetmap.org/search/' . $address, [
      'query' => [
        'email' => \Drupal::config('system.site')->get('mail'),
        'limit' => 1,
        'format' => 'json',
        'connect_timeout' => 5,
      ],
    ]);

    try {
      $result = Json::decode(\Drupal::httpClient()->get($url->toString())->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    $location = [];

    if (empty($result[0])) {
      return FALSE;
    }
    else {
      $location['location'] = [
        'lat' => $result[0]['lat'],
        'lng' => $result[0]['lon'],
      ];
    }

    if (!empty($result[0]['boundingbox'])) {
      $location['boundary'] = [
        'lat_north_east' => $result[0]['boundingbox'][1],
        'lng_north_east' => $result[0]['boundingbox'][3],
        'lat_south_west' => $result[0]['boundingbox'][0],
        'lng_south_west' => $result[0]['boundingbox'][2],
      ];
    }

    if (!empty($result[0]['display_name'])) {
      $location['address'] = $result[0]['display_name'];
    }

    return $location;
  }

}
