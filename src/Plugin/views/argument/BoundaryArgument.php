<?php

namespace Drupal\geolocation\Plugin\views\argument;

use Drupal\geolocation\GeolocationCore;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler for geolocation boundary.
 *
 * Argument format should be in the following format:
 * NE-Lat,NE-Lng,SW-Lat,SW-Lng, so "11.1,33.3,55.5,77.7".
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("geolocation_argument_boundary")
 */
class BoundaryArgument extends ArgumentPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The GeolocationCore object.
   *
   * @var \Drupal\geolocation\GeolocationCore
   */
  protected $geolocationCore;

  /**
   * Constructs a Handler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\geolocation\GeolocationCore $geolocation_core
   *   The GeolocationCore object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GeolocationCore $geolocation_core) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->geolocationCore = $geolocation_core;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\geolocation\GeolocationCore $geolocation_core */
    $geolocation_core = $container->get('geolocation.core');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $geolocation_core
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['description']['#markup'] .= $this->t('<br/>Boundary format should be in a NE-Lat,NE-Lng,SW-Lat,SW-Lng format: <strong>"11.1,33.3,55.5,77.7"</strong> .');
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $values = $this->getParsedBoundary();
    if (!($this->query instanceof Sql)) {
      return;
    }

    if (empty($values)) {
      return;
    }

    // Get the field alias.
    $lat_north_east = $values['lat_north_east'];
    $lng_north_east = $values['lng_north_east'];
    $lat_south_west = $values['lat_south_west'];
    $lng_south_west = $values['lng_south_west'];

    if (
      !is_numeric($lat_north_east)
      || !is_numeric($lng_north_east)
      || !is_numeric($lat_south_west)
      || !is_numeric($lng_south_west)
    ) {
      return;
    }

    $this->query->addWhereExpression(
      $group_by,
      $this->geolocationCore->getBoundaryQueryFragment($this->ensureMyTable(), $this->realField, $lat_north_east, $lng_north_east, $lat_south_west, $lng_south_west)
    );
  }

  /**
   * Processes the passed argument into an array of relevant geolocation data.
   *
   * @return array|bool
   *   The calculated values.
   */
  public function getParsedBoundary() {
    // Cache the vales so this only gets processed once.
    static $values;

    if (!isset($values)) {
      // Process argument values into an array.
      preg_match('/^([0-9\-.]+),+([0-9\-.]+),+([0-9\-.]+),+([0-9\-.]+)(.*$)/', $this->getValue(), $values);
      // Validate and return the passed argument.
      $values = is_array($values) ? [
        'lat_north_east' => (isset($values[1]) && is_numeric($values[1]) && $values[1] >= -90 && $values[1] <= 90) ? floatval($values[1]) : FALSE,
        'lng_north_east' => (isset($values[2]) && is_numeric($values[2]) && $values[2] >= -180 && $values[2] <= 180) ? floatval($values[2]) : FALSE,
        'lat_south_west' => (isset($values[2]) && is_numeric($values[3]) && $values[3] >= -90 && $values[3] <= 90) ? floatval($values[3]) : FALSE,
        'lng_south_west' => (isset($values[2]) && is_numeric($values[4]) && $values[4] >= -180 && $values[4] <= 180) ? floatval($values[4]) : FALSE,
      ] : FALSE;
    }
    return $values;
  }

}
