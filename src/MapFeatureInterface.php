<?php

namespace Drupal\geolocation;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for geolocation MapFeature plugins.
 */
interface MapFeatureInterface extends PluginInspectionInterface {

  /**
   * Provide a populated settings array.
   *
   * @return array
   *   The settings array with the default map settings.
   */
  public static function getDefaultSettings();

  /**
   * Alter single location entry.
   *
   * @param array $location
   *   Current location.
   * @param array $tokens
   *   Optionally tokens.
   */
  public function alterLocation(array &$location, array $tokens = []);

  /**
   * Provide map feature specific settings ready to handover to JS.
   *
   * @param array $settings
   *   Current general map settings. Might contain unrelated settings as well.
   *
   * @return array
   *   An array only containing keys defined in this plugin.
   */
  public function getSettings(array $settings);

  /**
   * Provide a summary array to use in field formatters.
   *
   * @param array $settings
   *   The current map settings.
   *
   * @return array
   *   An array to use as field formatter summary.
   */
  public function getSettingsSummary(array $settings);

  /**
   * Provide a generic map settings form array.
   *
   * @param array $settings
   *   The current map settings.
   * @param string $form_prefix
   *   Form specific optional prefix.
   *
   * @return array
   *   A form array to be integrated in whatever.
   */
  public function getSettingsForm(array $settings, $form_prefix = '');

  /**
   * Return all Drupal libraries required by this map feature.
   *
   * 'settings' => JS settings.
   * 'libraries' => array of libraries.
   *
   * @param array $settings
   *   The current map settings.
   *
   * @return array
   *   Drupal libraries.
   */
  public function attachments(array $settings);

}
