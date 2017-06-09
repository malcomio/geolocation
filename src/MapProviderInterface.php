<?php

namespace Drupal\geolocation;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for geolocation MapProvider plugins.
 */
interface MapProviderInterface extends PluginInspectionInterface {

  /**
   * Provide a populated settings array.
   *
   * @return array
   *   The settings array with the default map settings.
   */
  public static function getDefaultSettings();

  /**
   * Provide map provider specific settings ready to handover to JS.
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
   * Validate the form elements defined above.
   *
   * @param array $form
   *   Values to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current Formstate.
   * @param string|null $prefix
   *   Form state prefix if needed.
   */
  public function validateSettingsForm(array $form, FormStateInterface $form_state, $prefix = NULL);

  /**
   * Return all Drupal libraries required by this map provider.
   *
   * @return array
   *   Drupal libraries.
   */
  public function getLibraries();

}
