<?php

namespace Drupal\geolocation\Plugin\geolocation\LocationInput;

use Drupal\geolocation\LocationInputInterface;
use Drupal\geolocation\LocationInputBase;

/**
 * Location based proximity center.
 *
 * @LocationInput(
 *   id = "client_location",
 *   name = @Translation("Client location"),
 *   description = @Translation("If client provides location, use it."),
 * )
 */
class ClientLocation extends LocationInputBase implements LocationInputInterface {

  /**
   * {@inheritdoc}
   */
  public function getForm($center_option_id, array $center_option_settings, $context = NULL, array $default_value = NULL) {
    $form = parent::getForm($center_option_id, $center_option_settings, $context, $default_value);

    $identifier = uniqid($center_option_id);

    if (!empty($form['coordinates'])) {

      $form['coordinates']['#attributes'] = [
        'class' => [
          $identifier,
          'location-input-client-location',
          'visually-hidden',
        ],
      ];

      $form['coordinates']['#attached'] = [
        'library' => [
          'geolocation/location_input.client_location',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'locationInput' => [
              'clientLocation' => [
                $identifier,
              ],
            ],
          ],
        ],
      ];
    }

    return $form;
  }

}
