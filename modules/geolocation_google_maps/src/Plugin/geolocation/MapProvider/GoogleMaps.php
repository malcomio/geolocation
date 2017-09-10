<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\geolocation\MapProviderBase;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides Google Maps.
 *
 * @MapProvider(
 *   id = "google_maps",
 *   name = @Translation("Google Maps"),
 *   description = @Translation("You do require an API key for this plugin to work."),
 * )
 */
class GoogleMaps extends MapProviderBase {

  /**
   * Google map style - Roadmap.
   *
   * @var string
   */
  public static $ROADMAP = 'ROADMAP';

  /**
   * Google map style - Satellite.
   *
   * @var string
   */
  public static $SATELLITE = 'SATELLITE';

  /**
   * Google map style - Hybrid.
   *
   * @var string
   */
  public static $HYBRID = 'HYBRID';

  /**
   * Google map style - Terrain.
   *
   * @var string
   */
  public static $TERRAIN = 'TERRAIN';

  /**
   * Google maps url with default parameters.
   *
   * @var string
   */
  public static $GOOGLEMAPSAPIURL = 'https://maps.googleapis.com/maps/api/js';

  /**
   * Google map max zoom level.
   *
   * @var int
   */
  public static $MAXZOOMLEVEL = 18;

  /**
   * Google map min zoom level.
   *
   * @var int
   */
  public static $MINZOOMLEVEL = 0;

  /**
   * Return all module and custom defined parameters.
   *
   * @return array
   *   Parameters
   */
  public function getGoogleMapsApiParameters() {
    $config = \Drupal::config('geolocation.settings');
    $geolocation_parameters = [
      'callback' => 'Drupal.geolocation.googleCallback',
      'key' => $config->get('google_map_api_key'),
    ];
    $module_parameters = \Drupal::moduleHandler()->invokeAll('geolocation_google_maps_parameters') ?: [];
    $custom_parameters = $config->get('google_map_custom_url_parameters') ?: [];

    $parameters = array_replace_recursive($custom_parameters, $module_parameters, $geolocation_parameters);

    if (!empty($parameters['client'])) {
      unset($parameters['key']);
    }

    return $parameters;
  }

  /**
   * Return the fully build URL to load Google Maps API.
   *
   * @return string
   *   Google Maps API URL
   */
  public function getGoogleMapsApiUrl() {
    $parameters = [];
    foreach ($this->getGoogleMapsApiParameters() as $parameter => $value) {
      $parameters[$parameter] = is_array($value) ? implode(',', $value) : $value;
    }
    $url = Url::fromUri(static::$GOOGLEMAPSAPIURL, [
      'query' => $parameters,
      'https' => TRUE,
    ]);
    return $url->toString();
  }

  /**
   * An array of all available map types.
   *
   * @return array
   *   The map types.
   */
  private function getMapTypes() {
    $mapTypes = [
      static::$ROADMAP => 'Road map view',
      static::$SATELLITE => 'Google Earth satellite images',
      static::$HYBRID => 'A mixture of normal and satellite views',
      static::$TERRAIN => 'A physical map based on terrain information',
    ];

    return array_map([$this, 't'], $mapTypes);
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'type' => static::$ROADMAP,
      'zoom' => 10,
      'minZoom' => static::$MINZOOMLEVEL,
      'maxZoom' => static::$MAXZOOMLEVEL,
      'rotateControl' => FALSE,
      'mapTypeControl' => TRUE,
      'streetViewControl' => TRUE,
      'zoomControl' => TRUE,
      'fullscreenControl' => FALSE,
      'scrollwheel' => TRUE,
      'disableDoubleClickZoom' => FALSE,
      'draggable' => TRUE,
      'height' => '400px',
      'width' => '100%',
      'info_auto_display' => TRUE,
      'marker_icon_path' => '',
      'disableAutoPan' => TRUE,
      'style' => '',
      'preferScrollingToZooming' => FALSE,
      'gestureHandling' => 'auto',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    $settings['marker_icon_path'] = \Drupal::token()->replace($settings['marker_icon_path']);

    foreach ($settings as $key => $setting) {
      if (!isset($default_settings[$key])) {
        unset($settings[$key]);
      }
    }

    // Convert JSON string to actual array before handing to Renderer.
    if (!empty($settings['style'])) {
      $json = json_decode($settings['style']);
      if (is_array($json)) {
        $settings['style'] = $json;
      }
    }

    foreach ($this->mapFeatureManager->getMapFeaturesByMapType('google_maps') as $feature_id => $feature_definition) {
      if ($settings[$feature_id]['enabled']) {
        $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
        $settings[$feature_id] = $feature->getSettings($settings[$feature_id]['settings']);
      }
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $types = $this->getMapTypes();
    $summary = [];
    $summary[] = $this->t('Map Type: @type', ['@type' => $types[$settings['type']]]);
    $summary[] = $this->t('Zoom level: @zoom', ['@zoom' => $settings['zoom']]);
    $summary[] = $this->t('Height: @height', ['@height' => $settings['height']]);
    $summary[] = $this->t('Width: @width', ['@width' => $settings['width']]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $settings += self::getDefaultSettings();
    $parents_string = implode('][', $parents);
    $form = [
      '#type' => 'details',
      '#title' => t('Google Maps settings'),
      '#description' => t('Additional map settings provided by Google Maps'),
    ];

    /*
     * General settings.
     */
    $form['general_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General'),
    ];
    $form['height'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['height'],
    ];
    $form['width'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['width'],
    ];
    $form['type'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'select',
      '#title' => $this->t('Default map type'),
      '#options' => $this->getMapTypes(),
      '#default_value' => $settings['type'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['zoom'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The initial resolution at which to display the map, where zoom 0 corresponds to a map of the Earth fully zoomed out, and higher zoom levels zoom in at a higher resolution.'),
      '#default_value' => $settings['zoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['maxZoom'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'select',
      '#title' => $this->t('Max Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The maximum zoom level which will be displayed on the map. If omitted, or set to null, the maximum zoom from the current map type is used instead.'),
      '#default_value' => $settings['maxZoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['minZoom'] = [
      '#group' => $parents_string . '][general_settings',
      '#type' => 'select',
      '#title' => $this->t('Min Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The minimum zoom level which will be displayed on the map. If omitted, or set to null, the minimum zoom from the current map type is used instead.'),
      '#default_value' => $settings['minZoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];

    /*
     * Control settings.
     */

    $form['control_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Controls'),
    ];
    $form['mapTypeControl'] = [
      '#group' => $parents_string . '][control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Map type control'),
      '#description' => $this->t('Allow the user to change the map type.'),
      '#default_value' => $settings['mapTypeControl'],
    ];
    $form['streetViewControl'] = [
      '#group' => $parents_string . '][control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Street view control'),
      '#description' => $this->t('Allow the user to switch to google street view.'),
      '#default_value' => $settings['streetViewControl'],
    ];
    $form['zoomControl'] = [
      '#group' => $parents_string . '][control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Zoom control'),
      '#description' => $this->t('Show zoom controls.'),
      '#default_value' => $settings['zoomControl'],
    ];
    $form['rotateControl'] = [
      '#group' => $parents_string . '][control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Rotate control'),
      '#description' => $this->t('Show rotate control.'),
      '#default_value' => $settings['rotateControl'],
    ];
    $form['fullscreenControl'] = [
      '#group' => $parents_string . '][control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Fullscreen control'),
      '#description' => $this->t('Show fullscreen control.'),
      '#default_value' => $settings['fullscreenControl'],
    ];

    /*
     * Behavior settings.
     */
    $form['behavior_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Behavior'),
    ];

    $form['scrollwheel'] = [
      '#group' => $parents_string . '][behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Scrollwheel'),
      '#description' => $this->t('Allow the user to zoom the map using the scrollwheel.'),
      '#default_value' => $settings['scrollwheel'],
    ];
    $form['gestureHandling'] = [
      '#group' => $parents_string . '][behavior_settings',
      '#type' => 'select',
      '#title' => $this->t('Gesture Handling'),
      '#default_value' => $settings['gestureHandling'],
      '#description' => $this->t('Define how to handle interactions with map on mobile. Read the <a href=":introduction">introduction</a> for handling or the <a href=":details">details</a>, <i>available as of v3.27 / Nov. 2016</i>.', [
        ':introduction' => 'https://googlegeodevelopers.blogspot.de/2016/11/smart-scrolling-comes-to-mobile-web-maps.html',
        ':details' => 'https://developers.google.com/maps/documentation/javascript/3.exp/reference#MapOptions',
      ]),
      '#options' => [
        'auto' => $this->t('auto (default)'),
        'cooperative' => $this->t('cooperative'),
        'greedy' => $this->t('greedy'),
        'none' => $this->t('none'),
      ],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['draggable'] = [
      '#group' => $parents_string . '][behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Draggable'),
      '#description' => $this->t('Allow the user to change the field of view. <i>Deprecated as of v3.27 / Nov. 2016 in favor of gesture handling described above.</i>.'),
      '#default_value' => $settings['draggable'],
    ];
    $form['preferScrollingToZooming'] = [
      '#group' => $parents_string . '][behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Require the user to click the map once to zoom, to ease scrolling behavior.'),
      '#description' => $this->t('Note: this is only relevant, when the Scrollwheel option is enabled.'),
      '#default_value' => $settings['preferScrollingToZooming'],
    ];
    $form['disableDoubleClickZoom'] = [
      '#group' => $parents_string . '][behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Disable double click zoom'),
      '#description' => $this->t('Disables the double click zoom functionality.'),
      '#default_value' => $settings['disableDoubleClickZoom'],
    ];

    $form['style'] = [
      '#title' => $this->t('JSON styles'),
      '#type' => 'textarea',
      '#default_value' => $settings['style'],
      '#description' => $this->t('A JSON encoded styles array to customize the presentation of the Google Map. See the <a href=":styling">Styled Map</a> section of the Google Maps website for further information.', [
        ':styling' => 'https://developers.google.com/maps/documentation/javascript/styling',
      ]),
    ];

    /*
     * Marker settings.
     */
    $form['marker_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Marker'),
    ];

    $form['info_auto_display'] = [
      '#group' => $parents_string . '][marker_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically show info text'),
      '#default_value' => $settings['info_auto_display'],
    ];
    $form['marker_icon_path'] = [
      '#group' => $parents_string . '][marker_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Marker icon path'),
      '#description' => $this->t('Set relative or absolute path to custom marker icon. Tokens supported. Empty for default.'),
      '#default_value' => $settings['marker_icon_path'],
    ];
    $form['disableAutoPan'] = [
      '#group' => $parents_string . '][marker_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Disable automatic panning of map when info bubble is opened.'),
      '#default_value' => $settings['disableAutoPan'],
    ];

    foreach ($this->mapFeatureManager->getMapFeaturesByMapType('google_maps') as $feature_id => $feature_definition) {
      $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
continue;
      if (empty($feature)) {
        continue;
      }

      $feature_enable_id = uniqid($feature_id . '_enabled');

      $feature_form = $feature->getSettingsForm($settings[$feature_id]['settings'] ?: [], $parents_string . '][' . $feature_id . '_settings');
      $feature_form['#states'] = [
        'visible' => [
          ':input[id="' . $feature_enable_id . '"]' => ['checked' => TRUE],
        ],
      ];
      $feature_form['#type'] = 'item';

      $form[$feature_id] = [
        '#type' => 'fieldset',
        '#title' => $feature_definition['name'],
        'enabled' => [
          '#title' => $this->t('Enable'),
          '#attributes' => [
            'id' => $feature_enable_id,
          ],
          '#description' => $feature_definition['description'],
          '#type' => 'checkbox',
          '#default_value' => (boolean) $settings[$feature_id]['enabled'],
        ],
        'settings' => $feature_form,
      ];
    }

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
  public function attachments(array $settings) {
    $libraries = ['geolocation_google_maps/geolocation.googlemapsapi'];

    $default_settings = self::getDefaultSettings();
    $settings = array_replace_recursive($default_settings, $settings);

    $attachments = [
      'library' => $libraries,
      'drupalSettings' => [
        'geolocation' => [
          'google_map_url' => $this->getGoogleMapsApiUrl(),
        ],
      ],
    ];

    foreach ($this->mapFeatureManager->getMapFeaturesByMapType('google_maps') as $feature_id => $feature_definition) {
      if (!empty($settings[$feature_id]['enabled'])) {
        $feature = $this->mapFeatureManager->getMapFeature($feature_id, []);
        $feature_attachments = array_merge_recursive($feature->attachments($settings[$feature_id]['settings']), $attachments);

        $a = 5;
      }
    }

    return $attachments;
  }

}
