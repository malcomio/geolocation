<?php

namespace Drupal\geolocation;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for geolocation MapCenter plugins.
 */
interface MapCenterInterface extends PluginInspectionInterface {

  /**
   * Provide a populated settings array.
   *
   * @return array
   *   The settings array with the default map settings.
   */
  public static function getDefaultSettings();

  /**
   * Provide MapCenter option specific settings.
   *
   * @param array $settings
   *   Current settings.
   *
   * @return array
   *   An array only containing keys defined in this plugin.
   */
  public function getSettings(array $settings);

  /**
   * Settings form by ID and context.
   *
   * @param integer $center_option_id
   *   MapCenter option ID.
   * @param array $context
   *   Current context.
   * @param array $settings
   *   The current option settings.
   * @param array $parents
   *   Form parents.
   *
   * @return array
   *   A form array to be integrated in whatever.
   */
  public function getSettingsForm($center_option_id, array $context, array $settings);

  /**
   * For one MapCenter (i.e. boundary filter), return all options (all filters).
   *
   * @param array $context
   *   Context like field formatter, field widget or view.
   *
   * @return array
   *   Available center options indexed by ID.
   */
  public function getAvailableMapCenterOptions(array $context);

  /**
   * Get map center.
   *
   * @@param integer $center_option_id
   *   MapCenter option ID.
   * @param array $center_option_settings
   *   The current feature settings.
   * @param array $context
   *   Context like field formatter, field widget or view.
   *
   * @return array
   *   Center definition.
   */
  public function getMapCenter($center_option_id, array $center_option_settings, array $context);

}
