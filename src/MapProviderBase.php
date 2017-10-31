<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MapProviderBase.
 *
 * @package Drupal\geolocation
 */
abstract class MapProviderBase extends PluginBase implements MapProviderInterface, ContainerFactoryPluginInterface {

  /**
   * Map feature manager.
   *
   * @var \Drupal\geolocation\MapFeatureManager
   */
  protected $mapFeatureManager;

  /**
   * Constructs a new GeocoderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\geolocation\MapFeatureManager $map_feature_manager
   *   Map feature manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MapFeatureManager $map_feature_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mapFeatureManager = $map_feature_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.geolocation.mapfeature')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'map_features' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = $this->getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    foreach ($settings as $key => $setting) {
      if (!isset($default_settings[$key])) {
        unset($settings[$key]);
      }
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $form = [];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings, $map_id) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $settings, $map_id) {
    foreach ($this->mapFeatureManager->getMapFeaturesByMapType($this->getPluginId()) as $feature_id => $feature_definition) {
      if (!empty($settings['map_features'][$feature_id]['enabled'])) {
        $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
        if ($feature) {
          if (!empty($settings['map_features'][$feature_id]['settings'])) {
            $feature_settings = $settings['map_features'][$feature_id]['settings'];
          }
          else {
            $feature_settings = $feature->getSettings([]);
          }
          $render_array = $feature->alterRenderArray($render_array, $feature_settings, $map_id);
        }
      }
    }

    return $render_array;
  }

}
