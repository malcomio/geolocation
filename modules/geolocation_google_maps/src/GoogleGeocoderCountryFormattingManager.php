<?php

namespace Drupal\geolocation_google_maps;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Search plugin manager.
 */
class GoogleGeocoderCountryFormattingManager extends DefaultPluginManager {

  /**
   * Constructs an GoogleGeocoderCountryFormattingManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/geolocation_google_maps/GoogleGeocoderCountryFormatting', $namespaces, $module_handler, 'Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingInterface', 'Drupal\geolocation_google_maps\Annotation\GoogleGeocoderCountryFormatting');
    $this->alterInfo('geolocation_google_geocoder_country_formatting_info');
    $this->setCacheBackend($cache_backend, 'geolocation_google_geocoder_country_formatting');
  }

  /**
   * Return Country plugin by country code.
   *
   * @param string $country_code
   *   Plugin ID.
   *
   * @return \Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingInterface|false
   *   Geocoder instance.
   */
  public function getCountry($country_code) {
    $country_code = strtolower($country_code);
    if ($this->hasDefinition($country_code)) {
      try {
        $instance = $this->createInstance($country_code);

      }
      catch (\Exception $e) {
        $instance = $this->createInstance('standard');
      }
    }
    else {
      $instance = $this->createInstance('standard');
    }

    /** @var \Drupal\geolocation_google_maps\GoogleGeocoderCountryFormattingInterface $instance */
    return $instance;
  }

}
