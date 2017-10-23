<?php

namespace Drupal\geolocation\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\address\FieldHelper;
use Drupal\address\LabelHelper;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a render element to display a geolocation map.
 *
 * Usage example:
 * @code
 * $form['map'] = [
 *   '#type' => 'geolocation_map',
 *   '#prefix' => $this->t('Geolocation Map Render Element'),
 *   '#description' => $this->t('Render element type "geolocation_map"'),
 *   '#maptype' => 'leaflet,
 *   '#centre' => [],
 *   '#uniqueid' => 'thisisanid',
 * ];
 * @endcode
 *
 * @FormElement("geolocation_input")
 */
class GeolocationInput extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processGeolocation'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'groupElements'],
        [$class, 'preRenderGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateGeolocation'],
      ],
      '#theme' => 'input__email',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Processes the address form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   *
   * @throws \InvalidArgumentException
   *   Thrown when #available_countries or #used_fields is malformed.
   */
  public static function processGeolocation(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $default_field_values = [
      'lat' => '',
      'lng' => '',
    ];

    if (!empty($element['#default_value'])) {
      $default_field_values = [
        'lat' => $element['#default_value']['lat'],
        'lng' => $element['#default_value']['lng'],
      ];
    }

    // Hidden lat,lng input fields.
    $element['latitude'] = [
      '#type' => 'textfield',
      '#title' => t('Latitude'),
      '#default_value' => $default_field_values['lat'],
      '#attributes' => [
        'class' => [
          'geolocation-map-input-latitude',
        ],
      ],
    ];
    $element['longitude'] = [
      '#type' => 'textfield',
      '#title' => t('Longitude'),
      '#default_value' => $default_field_values['lng'],
      '#attributes' => [
        'class' => [
          'geolocation-map-input-latitude',
        ],
      ],
    ];

    $element['#attributes'] = [
      'class' => [
        'geolocation-map-input',
      ],
    ];

    return $element;
  }

  /**
   * Groups elements with the same #group so that they can be inlined.
   */
  public static function groupElements(array $element) {
    $sort = [];
    foreach (Element::getVisibleChildren($element) as $key) {
      if (isset($element[$key]['#group'])) {
        // Copy the element to the container and remove the original.
        $group_index = $element[$key]['#group'];
        $container_key = 'container' . $group_index;
        $element[$container_key][$key] = $element[$key];
        unset($element[$key]);
        // Mark the container for sorting.
        if (!in_array($container_key, $sort)) {
          $sort[] = $container_key;
        }
      }
    }
    // Sort the moved elements, so that their #weight stays respected.
    foreach ($sort as $key) {
      uasort($element[$key], [SortArray::class, 'sortByWeightProperty']);
    }

    return $element;
  }

  /**
   * Form element validation handler for #type 'email'.
   *
   * Note that #maxlength and #required is validated by _form_validate() already.
   */
  public static function validateGeolocation(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if ($value !== '' && !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setError($element, t('The email address %mail is not valid.', ['%mail' => $value]));
    }
  }

}
