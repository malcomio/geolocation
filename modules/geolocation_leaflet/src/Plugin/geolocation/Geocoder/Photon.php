<?php

namespace Drupal\geolocation_leaflet\Plugin\geolocation\Geocoder;

use Drupal\geolocation\GeocoderBase;
use Drupal\geolocation\GeocoderInterface;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Url;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides the Photon.
 *
 * @Geocoder(
 *   id = "photon",
 *   name = @Translation("Photon"),
 *   description = @Translation("See https://photon.komoot.de for details."),
 *   locationCapable = true,
 *   boundaryCapable = true,
 *   frontendCapable = true,
 * )
 */
class Photon extends GeocoderBase implements GeocoderInterface {

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    parent::formAttachGeocoder($render_array, $element_name);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_leaflet/geocoder.photon',
        ],
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {
    if (empty($address)) {
      return FALSE;
    }

    $url = Url::fromUri('https://photon.komoot.de/api/' . $address, [
      'query' => [
        'q' => $address,
        'limit' => 1,
        'lang' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
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
