<?php

namespace Drupal\geolocation\Plugin\geolocation\Location;

use Drupal\views\Plugin\views\style\StylePluginBase;
use Drupal\geolocation\LocationInterface;
use Drupal\geolocation\LocationBase;

/**
 * Derive center from first row.
 *
 * @Location(
 *   id = "first_row",
 *   name = @Translation("View first row"),
 *   description = @Translation("Use geolocation field value from first row."),
 * )
 */
class FirstRow extends LocationBase implements LocationInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvailableLocationOptions(array $context) {
    $options = [];

    if (
      !empty($context['views_style'])
      && is_a($context['views_style'], StylePluginBase::class)
    ) {
      /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
      $views_style = $context['views_style'];
      if ($views_style->getPluginId() == 'maps_common') {
        $options['first_row'] = t('First row');
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getCoordinates($location_option_id, array $location_option_settings, array $context = []) {
    if (
      empty($context['views_style'])
      || !is_a($context['views_style'], StylePluginBase::class)
    ) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
    $views_style = $context['views_style'];

    if (empty($views_style->options['geolocation_field'])) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    $geolocation_field = $views_style->view->field[$views_style->options['geolocation_field']];

    if (empty($geolocation_field)) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    if (empty($views_style->view->result[0])) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    $entity = $geolocation_field->getEntity($views_style->view->result[0]);

    if (empty($entity)) {
      return parent::getCoordinates($location_option_id, $location_option_settings, $context);
    }

    if (isset($entity->{$geolocation_field->definition['field_name']})) {

      /** @var \Drupal\geolocation\Plugin\Field\FieldType\GeolocationItem $item */
      $item = $entity->{$geolocation_field->definition['field_name']}->first();

      return [
        'lat' => $item->get('lat')->getValue(),
        'lng' => $item->get('lng')->getValue(),
      ];
    }

    return parent::getCoordinates($location_option_id, $location_option_settings, $context);
  }

}
