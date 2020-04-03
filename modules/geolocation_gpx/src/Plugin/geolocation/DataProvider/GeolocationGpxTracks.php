<?php

namespace Drupal\geolocation_gpx\Plugin\geolocation\DataProvider;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Provides GPX Tracks.
 *
 * @DataProvider(
 *   id = "geolocation_gpx_tracks",
 *   name = @Translation("Geolocation GPX Tracks"),
 *   description = @Translation("GPX data."),
 * )
 */
class GeolocationGpxTracks extends GeolocationGpxBase {

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromItem(FieldItemInterface $fieldItem) {
    $gpxFile = $this->getGpxFileFromItem($fieldItem);
    if (empty($gpxFile)) {
      return FALSE;
    }

    $positions = [];

    foreach ($gpxFile->tracks as $track) {
      foreach ($track->segments as $segment) {
        foreach ($segment->points as $point) {
          $positions[] = [
            'lat' => $point->latitude,
            'lng' => $point->longitude,
          ];
        }
      }
    }

    return $positions;
  }

}
