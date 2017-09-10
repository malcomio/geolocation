<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\MapFeatureBase;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "marker_icon_path",
 *   name = @Translation("Google Marker Icon Path"),
 *   description = @Translation("Set path to marker icon with token support."),
 *   type = "google_maps",
 * )
 */
class GoogleMarkerIconPath extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'marker_icon_path' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    $return = [];

    $return['imagePath'] = $settings['marker_icon_path'];

    return [
      'markerClusterer' => $return,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $summary = [];
    $summary[] = $this->t('MarkerClusterer enabled');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, $form_prefix = '') {
    $form['description'] = [
      '#group' => $form_prefix,
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Various <a href=":url">examples</a> are available.', [':url' => 'https://developers.google.com/maps/documentation/javascript/marker-clustering']),
    ];
    $form['image_path'] = [
      '#group' => $form_prefix,
      '#title' => $this->t('Cluster image path'),
      '#type' => 'textfield',
      '#default_value' => $settings['image_path'],
      '#description' => $this->t("Set the marker image path. If omitted, the default image path %url will be used.", ['%url' => 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m']),
    ];
    $form['styles'] = [
      '#group' => $form_prefix,
      '#title' => $this->t('Styles of the Cluster'),
      '#type' => 'textarea',
      '#default_value' => $settings['styles'],
      '#description' => $this->t(
        'Set custom Cluster styles in JSON Format. Custom Styles have to be set for all 5 Cluster Images. See the <a href=":reference">reference</a> for details.',
        [
          ':reference' => 'https://googlemaps.github.io/js-marker-clusterer/docs/reference.html',
        ]
      ),
    ];

    $form['#element_validate'][] = [$this, 'validateSettingsForm'];

    return $form;
  }

  /**
   * Validate form.
   *
   * @param array $element
   *   Form element to check.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   * @param array $form
   *   Current form.
   */
  public function validateSettingsForm(array $element, FormStateInterface $form_state, array $form) {
    $values = $form_state->getValues();
    $parents = [];
    if (!empty($element['#parents'])) {
      $parents = $element['#parents'];
      $values = NestedArray::getValue($values, $parents);
    }

    $marker_clusterer_styles = $values['styles'];
    if (!empty($marker_clusterer_styles)) {
      $style_parents = $parents;
      $style_parents[] = 'styles';
      if (!is_string($marker_clusterer_styles)) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Please enter a JSON string as style.'));
      }
      $json_result = json_decode($marker_clusterer_styles);
      if ($json_result === NULL) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Decoding style JSON failed. Error: %error.', ['%error' => json_last_error()]));
      }
      elseif (!is_array($json_result)) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Decoded style JSON is not an array.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function attachments(array $settings) {
    return [
      'libraries' => [
        'geolocation/geolocation.markerclusterer',
      ],
      'settings' => $this->getSettings($settings),
    ];
  }

}
