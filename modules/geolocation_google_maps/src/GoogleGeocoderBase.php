<?php

namespace Drupal\geolocation_google_maps;

use Drupal\geolocation\GeocoderBase;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\geolocation\MapProviderManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class.
 *
 * @package Drupal\geolocation_google_places_api
 */
abstract class GoogleGeocoderBase extends GeocoderBase implements ContainerFactoryPluginInterface {

  protected $addressAtomicsMapping = [
    'houseNumber' => [
      'type' => 'street_number',
    ],
    'house' => [
      'type' => FALSE,
    ],
    'road' => [
      'type' => 'route',
    ],
    'village' => [
      'type' => FALSE,
    ],
    'suburb' => [
      'type' => FALSE,
    ],
    'city' => [
      'type' => 'locality ',
    ],
    'county' => [
      'type' => 'administrative_area_level_2 ',
    ],
    'postcode' => [
      'type' => 'postal_code',
    ],
    'stateDistrict' => [
      'type' => FALSE,
    ],
    'state' => [
      'type' => 'administrative_area_level_1 ',
    ],
    'region' => [
      'type' => FALSE,
    ],
    'island' => [
      'type' => FALSE,
    ],
    'country' => [
      'type' => 'country',
    ],
    'countryCode' => [
      'type' => 'country',
      'short' => TRUE,
    ],
  ];

  /**
   * Google maps provider.
   *
   * @var \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps
   */
  protected $googleMapsProvider;

  /**
   * GoogleGeocoderBase constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\geolocation\MapProviderManager $map_provider_manager
   *   Map provider management.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MapProviderManager $map_provider_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->googleMapsProvider = $map_provider_manager->getMapProvider('google_maps');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.geolocation.mapprovider')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSettings() {
    $default_settings = parent::getDefaultSettings();

    $default_settings['component_restrictions'] = [
      'route' => '',
      'locality' => '',
      'administrative_area' => '',
      'postal_code' => '',
      'country' => '',
    ];

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    parent::formAttachGeocoder($render_array, $element_name);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'drupalSettings' => [
          'geolocation' => [
            'google_map_url' => $this->googleMapsProvider->getGoogleMapsApiUrl(),
          ],
        ],
      ]
    );

    if (!empty($this->configuration['component_restrictions'])) {
      foreach ($this->configuration['component_restrictions'] as $component => $restriction) {
        if (empty($restriction)) {
          continue;
        }

        switch ($component) {
          case 'administrative_area':
            $component = 'administrativeArea';
            break;

          case 'postal_code':
            $component = 'postalCode';
            break;
        }

        $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
          empty($render_array['#attached']) ? [] : $render_array['#attached'],
          [
            'drupalSettings' => [
              'geolocation' => [
                'geocoder' => [
                  $this->getPluginId() => [
                    'componentRestrictions' => [
                      $component => $restriction,
                    ],
                  ],
                ],
              ],
            ],
          ]
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {

    $settings = $this->getSettings();

    $form = parent::getOptionsForm();

    $form += [
      'component_restrictions' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Component Restrictions'),
        '#description' => $this->t('See https://developers.google.com/maps/documentation/geocoding/intro#ComponentFiltering'),
        'route' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['route'],
          '#title' => $this->t('Route'),
          '#size' => 15,
        ],
        'locality' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['locality'],
          '#title' => $this->t('Locality'),
          '#size' => 15,
        ],
        'administrative_area' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['administrative_area'],
          '#title' => $this->t('Administrative Area'),
          '#size' => 15,
        ],
        'postal_code' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['postal_code'],
          '#title' => $this->t('Postal code'),
          '#size' => 5,
        ],
        'country' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['country'],
          '#title' => $this->t('Country'),
          '#size' => 5,
        ],
      ],
    ];

    return $form;
  }

}
