<?php

namespace Drupal\geolocation_google_maps\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a GoogleGeocoderCountryFormatting annotation object.
 *
 * @see \Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingManager
 * @see plugin_api
 *
 * @Annotation
 */
class GoogleGeocoderCountryFormatting extends Plugin {

  /**
   * The ID / Country code.
   *
   * @var string
   */
  public $id;
}
