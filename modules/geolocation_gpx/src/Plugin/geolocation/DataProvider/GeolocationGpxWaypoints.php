<?php

namespace Drupal\geolocation_gpx\Plugin\geolocation\DataProvider;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Provides GPX Waypoints.
 *
 * @DataProvider(
 *   id = "geolocation_gpx_waypoints",
 *   name = @Translation("Geolocation GPX Waypoints"),
 *   description = @Translation("GPX data."),
 * )
 */
class GeolocationGpxWaypoints extends GeolocationGpxBase {

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromItem(FieldItemInterface $fieldItem) {
    $gpxFile = $this->getGpxFileFromItem($fieldItem);
    if (empty($gpxFile)) {
      return FALSE;
    }

    $positions = [];

    foreach ($gpxFile->waypoints as $waypoint) {
      $positions[] = [
        'lat' => $waypoint->latitude,
        'lng' => $waypoint->longitude,
      ];
    }

    return $positions;
  }

}
