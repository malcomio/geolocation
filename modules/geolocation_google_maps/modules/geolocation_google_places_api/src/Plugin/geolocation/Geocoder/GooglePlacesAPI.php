<?php

namespace Drupal\geolocation_google_places_api\Plugin\geolocation\Geocoder;

use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\geolocation\GeocoderBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;

/**
 * Provides the Google Places API.
 *
 * @Geocoder(
 *   id = "google_places_api",
 *   name = @Translation("Google Places API"),
 *   description = @Translation("Attention: This Plugin needs you to follow Google Places API TOS and either use the Attribution Block or provide it yourself."),
 *   locationCapable = true,
 *   boundaryCapable = true,
 * )
 */
class GooglePlacesAPI extends GeocoderBase {

  /**
   * Google Maps Provider.
   *
   * @var \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps
   */
  protected $googleMapsProvider = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->googleMapsProvider = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider('google_maps');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSettings() {
    $default_settings = parent::getDefaultSettings();

    $default_settings['component_restrictions'] = [
      'route' => '',
      'locality' => '',
      'administrative_area' => '',
      'postal_code' => '',
      'country' => '',
    ];

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments($input_id) {
    $attachments = parent::attachments($input_id);

    $attachments = BubbleableMetadata::mergeAttachments(
      $attachments,
      [
        'library' => [
          'geolocation_google_places_api/geolocation_google_places_api.geocoder.googleplacesapi',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'google_map_url' => $this->googleMapsProvider->getGoogleMapsApiUrl(),
            'geocoder' => [
              'googlePlacesAPI' => [
                'inputIds' => [
                  $input_id,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    if (!empty($this->configuration['component_restrictions'])) {
      foreach ($this->configuration['component_restrictions'] as $component => $restriction) {
        if (empty($restriction)) {
          continue;
        }

        $attachments = BubbleableMetadata::mergeAttachments(
          $attachments,
          [
            'drupalSettings' => [
              'geolocation' => [
                'geocoder' => [
                  'googlePlacesAPI' => [
                    'restrictions' => [
                      $component => $restriction,
                    ],
                  ],
                ],
              ],
            ],
          ]
        );
      }
    }

    return $attachments;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {

    $form = parent::getOptionsForm();

    $settings = $this->getSettings();

    $form += [
      'description' => [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->getPluginDefinition()['description'],
      ],
      'component_restrictions' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Component Restrictions'),
        'route' => [
          '#type' => 'textfield',
          '#default_value' => $settings['route'],
          '#title' => $this->t('Route'),
          '#size' => 15,
        ],
        'locality' => [
          '#type' => 'textfield',
          '#default_value' => $settings['locality'],
          '#title' => $this->t('Locality'),
          '#size' => 15,
        ],
        'administrativeArea' => [
          '#type' => 'textfield',
          '#default_value' => $settings['administrativeArea'],
          '#title' => $this->t('Administrative Area'),
          '#size' => 15,
        ],
        'postalCode' => [
          '#type' => 'textfield',
          '#default_value' => $settings['postalCode'],
          '#title' => $this->t('Postal code'),
          '#size' => 5,
        ],
        'country' => [
          '#type' => 'textfield',
          '#default_value' => $settings['country'],
          '#title' => $this->t('Country'),
          '#size' => 5,
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    $settings = $this->getSettings();

    $render_array['geolocation_geocoder_google_places_api'] = [
      '#type' => 'textfield',
      '#title' => $settings['label'],
      '#description' => $settings['description'],
      '#description_display' => 'after',
      '#attributes' => [
        'class' => [
          'form-autocomplete',
          'geolocation-geocoder-google-places-api',
        ],
        'data-source-identifier' => $element_name,
      ],
    ];
    $render_array['geolocation_geocoder_google_places_api_state'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
      '#attributes' => [
        'class' => [
          'geolocation-geocoder-google-places-api-state',
        ],
        'data-source-identifier' => $element_name,
      ],
    ];

    $render_array['#attached'] = $this->attachments($element_name);
  }

  /**
   * {@inheritdoc}
   */
  public function formValidateInput(FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    if (
      !empty($input['geolocation_geocoder_google_places_api'])
      && empty($input['geolocation_geocoder_google_places_api_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_google_places_api']);

      if (empty($location_data)) {
        $form_state->setErrorByName('geolocation_geocoder_google_places_api', $this->t('Failed to geocode %input.', ['%input' => $input['geolocation_geocoder_google_places_api']]));
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function formProcessInput(array &$input, $element_name) {
    if (
      !empty($input['geolocation_geocoder_google_places_api'])
      && empty($input['geolocation_geocoder_google_places_api_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_google_places_api']);

      if (empty($location_data)) {
        $input['geolocation_geocoder_google_places_api_state'] = 0;
        return FALSE;
      }

      $input['geolocation_geocoder_google_places_api'] = $location_data['address'];
      $input['geolocation_geocoder_google_places_api_state'] = 1;

      return $location_data;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {

    if (empty($address)) {
      return FALSE;
    }

    $config = \Drupal::config('geolocation_google_maps.settings');

    $request_url = GoogleMaps::$GOOGLEMAPSAPIURLBASE;
    if ($config->get('china_mode')) {
      $request_url = GoogleMaps::$GOOGLEMAPSAPIURLBASECHINA;
    }
    $request_url .= '/maps/api/place/autocomplete/json?input=' . $address;

    $google_key = '';

    if (!empty($config->get('google_map_api_server_key'))) {
      $google_key = $config->get('google_map_api_server_key');
    }
    elseif (!empty($config->get('google_map_api_key'))) {
      $google_key = $config->get('google_map_api_key');
    }

    if (!empty($google_key)) {
      $request_url .= '&key=' . $google_key;
    }
    if (!empty($this->configuration['component_restrictions']['country'])) {
      $request_url .= '&components=country:' . $this->configuration['component_restrictions']['country'];
    }
    if (!empty($config->get('google_map_custom_url_parameters')['language'])) {
      $request_url .= '&language=' . $config->get('google_map_custom_url_parameters')['language'];
    }

    try {
      $result = Json::decode(\Drupal::httpClient()->request('GET', $request_url)->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    if (
      $result['status'] != 'OK'
      || empty($result['predictions'][0]['place_id'])
    ) {
      return FALSE;
    }

    try {
      $details_url = GoogleMaps::$GOOGLEMAPSAPIURLBASE;
      if ($config->get('china_mode')) {
        $details_url = GoogleMaps::$GOOGLEMAPSAPIURLBASECHINA;
      }
      $details_url .= '/maps/api/place/details/json?placeid=' . $result['predictions'][0]['place_id'];

      if (!empty($google_key)) {
        $details_url .= '&key=' . $google_key;
      }
      $details = Json::decode(\Drupal::httpClient()->request('GET', $details_url)->getBody());

    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    if (
      $details['status'] != 'OK'
      || empty($details['result']['geometry']['location'])
    ) {
      return FALSE;
    }

    return [
      'location' => [
        'lat' => $details['result']['geometry']['location']['lat'],
        'lng' => $details['result']['geometry']['location']['lng'],
      ],
      // TODO: Add viewport or build it if missing.
      'boundary' => [
        'lat_north_east' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lat'] + 0.005 : $details['result']['geometry']['viewport']['northeast']['lat'],
        'lng_north_east' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lng'] + 0.005 : $details['result']['geometry']['viewport']['northeast']['lng'],
        'lat_south_west' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lat'] - 0.005 : $details['result']['geometry']['viewport']['southwest']['lat'],
        'lng_south_west' => empty($details['result']['geometry']['viewport']) ? $details['result']['geometry']['location']['lng'] - 0.005 : $details['result']['geometry']['viewport']['southwest']['lng'],
      ],
      'address' => empty($details['result']['formatted_address']) ? '' : $details['result']['formatted_address'],
    ];
  }

}
