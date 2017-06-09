<?php

namespace Drupal\geolocation_demo\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Returns responses for geolocation_demo module routes.
 */
class GooglegeocoderWidget extends DemoWidget {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'geolocation_demo_googlegeocoder_widget';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $widget_form = $this->getWidgetForm('geolocation_googlegeocoder', $form, $form_state);

    $form['widget'] = $widget_form;

    return $form;
  }

}
