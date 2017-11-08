<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides Google Maps.
 *
 * @MapFeature(
 *   id = "map_type_style",
 *   name = @Translation("Google Map Type Style"),
 *   description = @Translation("Add map styling JSON."),
 *   type = "google_maps",
 * )
 */
class GoogleMapTypeStyle extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'style' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $settings = parent::getSettings($settings);

    // Convert JSON string to actual array before handing to Renderer.
    if (!empty($settings['style'])) {
      if (!is_array($settings['style'])) {
        $json = json_decode($settings['style']);
      }
      else {
        $json = $settings['style'];
      }

      if (is_array($json)) {
        $settings['style'] = $json;
      }
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {

    $settings = $this->getSettings($settings);

    $form['style'] = [
      '#title' => $this->t('JSON styles'),
      '#type' => 'textarea',
      '#default_value' => $settings['style'],
      '#description' => $this->t('A JSON encoded styles array to customize the presentation of the Google Map. See the <a href=":styling">Styled Map</a> section of the Google Maps website for further information.', [
        ':styling' => 'https://developers.google.com/maps/documentation/javascript/styling',
      ]),
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

    $json_style = $values['style'];
    if (!empty($json_style)) {
      $style_parents = $parents;
      $style_parents[] = 'styles';
      if (!is_string($json_style)) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Please enter a JSON string as style.'));
      }
      $json_result = json_decode($json_style);
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
  public function alterRenderArray(array $render_array, array $settings, $map_id = NULL) {
    $render_array = parent::alterRenderArray($render_array, $settings, $map_id);

    $settings = $this->getSettings($settings);

    if (
      !empty($settings['style'])
      && is_string($settings['style'])
    ) {
      $settings['style'] = json_decode($settings['style']);
    }


    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.maptypestyle',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $map_id => [
                'map_type_style' => [
                  'enable' => TRUE,
                  'style' => $settings['style'],
                ],
              ],
            ],
          ],
        ],
      ]
    );

    return $render_array;
  }

}
