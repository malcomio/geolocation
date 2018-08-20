<?php

namespace Drupal\geolocation\Plugin\geolocation\LocationInput;

use Drupal\geolocation\LocationInputInterface;
use Drupal\geolocation\LocationInputBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\geolocation\GeocoderManager;

/**
 * Location based proximity center.
 *
 * @LocationInput(
 *   id = "geocoder",
 *   name = @Translation("Geocoder address input"),
 *   description = @Translation("Enter an address and use the geocoded location."),
 * )
 */
class Geocoder extends LocationInputBase implements LocationInputInterface, ContainerFactoryPluginInterface {

  /**
   * Geocoder Manager.
   *
   * @var \Drupal\geolocation\GeocoderManager
   */
  protected $geocoderManager;

  /**
   * Geocoder constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\geolocation\GeocoderManager $geocoder_manager
   *   Geocoder Manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GeocoderManager $geocoder_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->geocoderManager = $geocoder_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.geolocation.geocoder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return array_replace_recursive(
      parent::getDefaultSettings(),
      [
        'plugin_id' => 'google_geocoding_api',
        'settings' => [],
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = $this->getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm($option_id = NULL, array $settings = [], $context = NULL) {
    $form = [];

    $settings = $this->getSettings($settings);

    /** @var \Drupal\geolocation\GeocoderManager $geocoder_manager */
    $geocoder_manager = \Drupal::service('plugin.manager.geolocation.geocoder');
    $geocoder_definitions = $geocoder_manager->getLocationCapableGeocoders();

    if ($geocoder_definitions) {
      $geocoder_options = [];
      foreach ($geocoder_definitions as $id => $definition) {
        $geocoder_options[$id] = $definition['name'];
      }

      $form['plugin_id'] = [
        '#type' => 'select',
        '#options' => $geocoder_options,
        '#title' => $this->t('Geocoder plugin'),
        '#default_value' => $settings['plugin_id'],
        '#ajax' => [
          'callback' => [get_class($geocoder_manager), 'addGeocoderSettingsFormAjax'],
          'wrapper' => 'geocoder-plugin-settings',
          'effect' => 'fade',
        ],
      ];

      if (!empty($settings['plugin_id'])) {
        $geocoder_plugin = $geocoder_manager->getGeocoder(
          $settings['plugin_id'],
          $settings['settings']
        );
      }
      elseif (current(array_keys($geocoder_options))) {
        $geocoder_plugin = $geocoder_manager->getGeocoder(current(array_keys($geocoder_options)));
      }

      if (!empty($geocoder_plugin)) {
        $geocoder_settings_form = $geocoder_plugin->getOptionsForm();
        if ($geocoder_settings_form) {
          $form['settings'] = $geocoder_settings_form;
        }
      }

      if (empty($form['settings'])) {
        $form['settings'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t("No settings available."),
        ];
      }

      $form['settings'] = array_replace_recursive($form['settings'], [
        '#flatten' => TRUE,
        '#prefix' => '<div id="geocoder-plugin-settings">',
        '#suffix' => '</div>',
      ]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getCoordinates($form_value, $option_id, array $option_settings, $context = NULL) {
    if (empty($form_value['geocoder'])) {
      return [];
    }

    $settings = $this->getSettings($option_settings);

    $location_data = $this->geocoderManager->getGeocoder($settings['plugin_id'], $settings['settings'])->formProcessInput($form_value['geocoder'], 'location_input_geocoder');
    if (empty($location_data['location'])) {
      return [];
    }
    else {
      return $location_data['location'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getForm($option_id, array $option_settings, $context = NULL, array $default_value = NULL) {
    $option_settings = $this->getSettings($option_settings);

    $form['geocoder'] = [
      '#type' => 'container',
    ];

    /** @var \Drupal\geolocation\GeocoderInterface $geocoder_plugin */
    $geocoder_plugin = $this->geocoderManager->getGeocoder(
      $option_settings['plugin_id'],
      $option_settings['settings']
    );

    $form['geocoder']['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($form['geocoder']['#attached']) ? [] : $form['geocoder']['#attached'],
      $geocoder_plugin->attachments('location_input_geocoder')
    );

    $geocoder_plugin->formAttachGeocoder($form['geocoder'], 'location_input_geocoder');

    return $form;
  }

}
