<?php

namespace Drupal\geolocation_address\Plugin\geolocation\DataProvider;

use Drupal\address\Plugin\Field\FieldType\AddressItem;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\geolocation\GeocoderManager;
use Drupal\views\Plugin\views\field\EntityField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\geolocation\DataProviderInterface;
use Drupal\geolocation\DataProviderBase;
use PredictHQ\AddressFormatter\Address;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides default address field.
 *
 * @DataProvider(
 *   id = "geolocation_address_field_provider",
 *   name = @Translation("Address Field"),
 *   description = @Translation("Address Field."),
 * )
 */
class AddressFieldProvider extends DataProviderBase implements DataProviderInterface {

  /**
   * Geocoder manager.
   *
   * @var \Drupal\geolocation\GeocoderManager
   */
  protected $geocoderManager = NULL;

  /**
   * Geocoder.
   *
   * @var \Drupal\geolocation\GeocoderInterface
   */
  protected $geocoder = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager, GeocoderManager $geocoder_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_field_manager);

    $this->geocoderManager = $geocoder_manager;

    if (!empty($configuration['geocoder'])) {
      $this->geocoder = $this->geocoderManager->createInstance($configuration['geocoder']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.geolocation.geocoder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isViewsGeoOption(FieldPluginBase $views_field) {
    if ($views_field instanceof EntityField) {
      /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
      $entityFieldManager = \Drupal::service('entity_field.manager');
      $field_map = $entityFieldManager->getFieldMap();
      if (
        !empty($field_map)
        &&!empty($views_field->configuration['entity_type'])
        && !empty($views_field->configuration['field_name'])
        && !empty($field_map[$views_field->configuration['entity_type']])
        && !empty($field_map[$views_field->configuration['entity_type']][$views_field->configuration['field_name']])
      ) {
        if ($field_map[$views_field->configuration['entity_type']][$views_field->configuration['field_name']]['type'] == 'address') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldGeoOption(FieldDefinitionInterface $fieldDefinition) {
    return ($fieldDefinition->getType() == 'address');
  }

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromItem(FieldItemInterface $item) {
    if (!($item instanceof AddressItem)) {
      return [];
    }

    if (empty($this->geocoder)) {
      return [];
    }

    /** @var \CommerceGuys\Addressing\Country\CountryRepositoryInterface $countryRepository */
    $countryRepository = \Drupal::service('address.country_repository');

    $country = $countryRepository->get($item->getCountryCode());

    $a = new Address();
    $a->setCity($item->getLocality())
      ->setCountryCode($item->getCountryCode())
      ->setCountry($country->getName())
      ->setPostcode($item->getPostalCode())
      ->setRoad($item->getAddressLine1())
      ->setState($item->getAdministrativeArea());

    $coordinates = $this->geocoder->geocode($a->format());
    return $coordinates['location'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $element = parent::getSettingsForm($settings, $parents);

    $geocoder_options = [];
    foreach ($this->geocoderManager->getDefinitions() as $geocoder_id => $geocoder_definition) {
      if (empty($geocoder_definition['locationCapable'])) {
        continue;
      }
      $geocoder_options[$geocoder_id] = $geocoder_definition['name'];
    }

    if (empty($geocoder_options)) {
      return [
        '#markup' => t('No geocoder option found'),
      ];
    }

    $element['geocoder'] = [
      '#type' => 'select',
      '#title' => $this->t('Geocoder'),
      '#options' => $geocoder_options,
      '#default_value' => empty($settings['geocoder']) ? key($geocoder_options) : $settings['geocoder'],
      '#description' => $this->t('Choose plugin to geocode address into coordinates.'),
      '#weight' => -1,
    ];

    return $element;
  }

}
