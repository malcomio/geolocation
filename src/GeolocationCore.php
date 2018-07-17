<?php

namespace Drupal\geolocation;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GeolocationCore.
 *
 * @package Drupal\geolocation
 */
class GeolocationCore implements ContainerInjectionInterface {
  use StringTranslationTrait;

  const EARTH_RADIUS_KM = 6371;
  const EARTH_RADIUS_MILE = 3959;
  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The GeocoderManager object.
   *
   * @var \Drupal\geolocation\GeocoderManager
   */
  protected $geocoderManager;

  /**
   * The MapProviderManager object.
   *
   * @var \Drupal\geolocation\MapProviderManager
   */
  protected $mapProviderManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   A module handler.
   * @param \Drupal\geolocation\GeocoderManager $geocoder_manager
   *   The GeocoderManager object.
   * @param \Drupal\geolocation\MapProviderManager $map_provider_manager
   *   The MapProviderManager object.
   */
  public function __construct(ModuleHandlerInterface $module_handler, GeocoderManager $geocoder_manager, MapProviderManager $map_provider_manager) {
    $this->moduleHandler = $module_handler;
    $this->geocoderManager = $geocoder_manager;
    $this->mapProviderManager = $map_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('plugin.manager.geolocation.geocoder'),
      $container->get('plugin.manager.geolocation.mapprovider')
    );
  }

  /**
   * Return current geocoder manager.
   *
   * @return \Drupal\geolocation\GeocoderManager
   *   Geocoder manager.
   */
  public function getGeocoderManager() {
    return $this->geocoderManager;
  }

  /**
   * Gets the query fragment for adding a proximity field to a query.
   *
   * @param string $table_name
   *   The proximity table name.
   * @param string $field_id
   *   The proximity field ID.
   * @param string $filter_lat
   *   The latitude to filter for.
   * @param string $filter_lng
   *   The longitude to filter for.
   * @param int $earth_radius
   *   Filter radius.
   *
   * @return string
   *   The fragment to enter to actual query.
   */
  public function getProximityQueryFragment($table_name, $field_id, $filter_lat, $filter_lng, $earth_radius = self::EARTH_RADIUS_KM) {

    // Define the field names.
    $field_latsin = "{$table_name}.{$field_id}_lat_sin";
    $field_latcos = "{$table_name}.{$field_id}_lat_cos";
    $field_lng    = "{$table_name}.{$field_id}_lng_rad";

    // deg2rad() is sensitive to empty strings. Replace with integer zero.
    $filter_lat = empty($filter_lat) ? 0 : $filter_lat;
    $filter_lng = empty($filter_lng) ? 0 : $filter_lng;

    // Pre-calculate filter values.
    $filter_latcos = cos(deg2rad($filter_lat));
    $filter_latsin = sin(deg2rad($filter_lat));
    $filter_lng    = deg2rad($filter_lng);

    return "(
      ACOS(LEAST(1,
        $filter_latcos
        * $field_latcos
        * COS( $filter_lng - $field_lng  )
        +
        $filter_latsin
        * $field_latsin
      )) * $earth_radius
    )";
  }

  /**
   * Gets the query fragment for adding a boundary field to a query.
   *
   * @param string $table_name
   *   The proximity table name.
   * @param string $field_id
   *   The proximity field ID.
   * @param string $filter_lat_north_east
   *   The latitude to filter for.
   * @param string $filter_lng_north_east
   *   The longitude to filter for.
   * @param string $filter_lat_south_west
   *   The latitude to filter for.
   * @param string $filter_lng_south_west
   *   The longitude to filter for.
   *
   * @return string
   *   The fragment to enter to actual query.
   */
  public function getBoundaryQueryFragment($table_name, $field_id, $filter_lat_north_east, $filter_lng_north_east, $filter_lat_south_west, $filter_lng_south_west) {
    // Define the field name.
    $field_lat = "{$table_name}.{$field_id}_lat";
    $field_lng = "{$table_name}.{$field_id}_lng";

    /*
     * Map shows a map, not a globe. Therefore it will never flip over
     * the poles, but it will move across -180°/+180° longitude.
     * So latitude will always have north larger than south, but east not
     * necessarily larger than west.
     */
    return "($field_lat BETWEEN $filter_lat_south_west AND $filter_lat_north_east)
      AND
      (
        ($filter_lng_south_west < $filter_lng_north_east AND $field_lng BETWEEN $filter_lng_south_west AND $filter_lng_north_east)
        OR
        (
          $filter_lng_south_west > $filter_lng_north_east AND (
            $field_lng BETWEEN $filter_lng_south_west AND 180 OR $field_lng BETWEEN -180 AND $filter_lng_north_east
          )
        )
      )
    ";
  }

}
