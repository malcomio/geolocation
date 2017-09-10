<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Config;

/**
 * Class MapFeatureBase.
 *
 * @package Drupal\geolocation
 */
abstract class MapFeatureBase extends PluginBase implements MapFeatureInterface, ContainerFactoryPluginInterface {

  /**
   * Geolocation settings config instance.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $geolocationSettings;

  /**
   * Constructs a new GeocoderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\Config $config
   *   The 'geolocation.settings' config.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Config $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->geolocationSettings = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')->get('geolocation.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    return [];
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
  public function getSettingsForm(array $settings, $form_prefix = '') {
    $form = [];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function alterLocation(array &$location, array $tokens = []) {
    return $location;
  }

}
