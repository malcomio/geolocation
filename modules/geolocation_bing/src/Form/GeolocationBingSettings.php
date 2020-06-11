<?php

namespace Drupal\geolocation_bing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the GeolocationBingSettings form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class GeolocationBingSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('geolocation_bing.settings');

    $form['#tree'] = TRUE;

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bing Maps API key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Bing requires users to use a valid API key. See <a href="https://docs.microsoft.com/en-us/bingmaps/getting-started/bing-maps-dev-center-help/getting-a-bing-maps-key">Getting a Bing Maps Key</a> for more information.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geolocation_bing_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'geolocation_bing.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('geolocation_bing.settings');
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();

    // Confirmation on form submission.
    \Drupal::messenger()->addMessage($this->t('The configuration options have been saved.'));

    drupal_flush_all_caches();
  }

}
