<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MapCenterBase.
 *
 * @package Drupal\geolocation
 */
abstract class MapCenterBase extends PluginBase implements MapCenterInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
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
    $default_settings = $this->getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm($option_id = NULL, array $context = NULL, array $settings = NULL) {
    $form = [];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSettingsForm(array $values, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function getAvailableMapCenterOptions(array $context) {
    return [
      $this->getPluginId() => $this->getPluginDefinition()['name'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context = []) {
    return [];
  }

}
