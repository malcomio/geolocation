<?php

namespace Drupal\geolocation_leaflet\Plugin\geolocation\MapFeature;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides map tile layer support.
 *
 * @MapFeature(
 *   id = "leaflet_tile_layer",
 *   name = @Translation("Tile Layer - Providers"),
 *   description = @Translation("Select a map tile layer."),
 *   type = "leaflet",
 * )
 */
class LeafletTileLayer extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'tile_layer_provider' => 'OpenStreetMap.Mapnik',
    ];
  }

  /**
   * Return options array for tile provider.
   *
   * @param string $provider
   *   Map tile provider selected.
   *
   * @return array
   *   Options form.
   */
  public static function getOptionsForm($provider) {

    $form = [
      '#prefix' => '<div id="tile-provider-settings">',
      '#suffix' => '</div>',
    ];
    switch ($provider) {
      case 'Thunderforest':
        $title = t('API key');
        $url = 'https://www.thunderforest.com/';
        $name = 'apikey';
        break;

      case 'MapBox':
        $title = t('Access Token');
        $url = 'https://www.mapbox.com/';
        $name = 'accessToken';
        break;

      case 'HERE':
        $form['app_id'] = [
          '#type' => 'textfield',
          '#title' => t('APP ID'),
          '#default_value' => '',
        ];
        $title = t('APP Code');
        $url = 'http://developer.here.com/';
        $name = 'app_code';
        break;

      case 'GeoportailFrance':
        $title = t('API key');
        $url = 'http://professionnels.ign.fr/ign/contrats/';
        $name = 'apikey';
        break;

      default:
        return $form;
    }

    $form[$name] = [
      '#type' => 'textfield',
      '#title' => $title,
      '#default_value' => '',
      '#description' => t('Get your API Key here <a href="@url">@provider</a>.', ['@url' => $url, '@provider' => $provider]),
    ];

    return $form;
  }

  /**
   * Return settings array for tile provider after select change.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current From State.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Settings form.
   */
  public static function addTileProviderSettingsFormAjax(array $form, FormStateInterface $form_state) {

    $ajax_response = new AjaxResponse();

    $triggering_element_value = $form_state->getTriggeringElement()['#value'];
    $provider = explode('.', $triggering_element_value)[0];

    $form = LeafletTileLayer::getOptionsForm($provider);
    $ajax_response->addCommand(new ReplaceCommand('#tile-provider-settings', $form));

    return $ajax_response;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = array_replace_recursive(
      self::getDefaultSettings(),
      $settings
    );

    $form['tile_layer_provider'] = [
      '#type' => 'select',
      '#options' => $this->getTileProviders(),
      '#default_value' => $settings['tile_layer_provider'],
      '#ajax' => [
        'callback' => [$this, 'addTileProviderSettingsFormAjax'],
        'wrapper' => 'tile-provider-settings',
        'effect' => 'fade',
      ],
    ];

    $provider = explode('.', $settings['tile_layer_provider'])[0];
    $form['tile_provider_options'] = LeafletTileLayer::getOptionsForm($provider);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings, array $context = []) {
    $render_array = parent::alterMap($render_array, $feature_settings, $context);

    $feature_settings = $this->getSettings($feature_settings);

    $tileLayer = [
      'enable' => TRUE,
      'tileLayerProvider' => $feature_settings['tile_layer_provider'],
    ];
    if (isset($feature_settings['tile_provider_options'])) {
      $tileLayer['tileLayerOptions'] = $feature_settings['tile_provider_options'];
    }
    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_leaflet/mapfeature.tilelayer',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'leaflet_tile_layer' => $tileLayer,
              ],
            ],
          ],
        ],
      ]
    );

    return $render_array;
  }

  /**
   * Provide some available tile providers.
   *
   * @return array
   *   An array containing tile provider IDs.
   */
  private function getTileProviders() {
    return [
      'OpenStreetMap' => [
        'OpenStreetMap.Mapnik' => 'OpenStreetMap Mapnik',
        'OpenStreetMap.BlackAndWhite' => 'OpenStreetMap BlackAndWhite',
        'OpenStreetMap.DE' => 'OpenStreetMap DE',
        'OpenStreetMap.CH' => 'OpenStreetMap CH',
        'OpenStreetMap.France' => 'OpenStreetMap France',
        'OpenStreetMap.HOT' => 'OpenStreetMap HOT',
        'OpenStreetMap.BZH' => 'OpenStreetMap BZH',
      ],
      'OpenTopoMap' => [
        'OpenTopoMap' => 'OpenTopoMap',
      ],
      'Thunderforest' => [
        'Thunderforest.OpenCycleMap' => 'Thunderforest OpenCycleMap',
        'Thunderforest.Transport' => 'Thunderforest Transport',
        'Thunderforest.TransportDark' => 'Thunderforest TransportDark',
        'Thunderforest.SpinalMap' => 'Thunderforest SpinalMap',
        'Thunderforest.Landscape' => 'Thunderforest Landscape',
        'Thunderforest.Outdoors' => 'Thunderforest Outdoors',
        'Thunderforest.Pioneer' => 'Thunderforest Pioneer',
      ],
      'OpenMapSurfer' => [
        'OpenMapSurfer.Roads' => 'OpenMapSurfer Roads',
        'OpenMapSurfer.Grayscale' => 'OpenMapSurfer Grayscale',
      ],
      'Hydda' => [
        'Hydda.Full' => 'Hydda Full',
        'Hydda.Base' => 'Hydda Base',
      ],
      'MapBox' => [
        'MapBox' => 'MapBox',
      ],
      'Stamen' => [
        'Stamen.Toner' => 'Stamen Toner',
        'Stamen.TonerBackground' => 'Stamen TonerBackground',
        'Stamen.TonerLite' => 'Stamen TonerLite',
        'Stamen.Watercolor' => 'Stamen Watercolor',
        'Stamen.Terrain' => 'Stamen Terrain',
        'Stamen.TerrainBackground' => 'Stamen TerrainBackground',
        'Stamen.TopOSMRelief' => 'Stamen TopOSMRelief',
      ],
      'Esri' => [
        'Esri.WorldStreetMap' => 'Esri WorldStreetMap',
        'Esri.DeLorme' => 'Esri DeLorme',
        'Esri.WorldTopoMap' => 'Esri WorldTopoMap',
        'Esri.WorldImagery' => 'Esri WorldImagery',
        'Esri.WorldTerrain' => 'Esri WorldTerrain',
        'Esri.WorldShadedRelief' => 'Esri WorldShadedRelief',
        'Esri.WorldPhysical' => 'Esri WorldPhysical',
        'Esri.OceanBasemap' => 'Esri OceanBasemap',
        'Esri.NatGeoWorldMap' => 'Esri NatGeoWorldMap',
        'Esri.WorldGrayCanvas' => 'Esri WorldGrayCanvas',
      ],
      'HERE' => [
        'HERE.normalDay' => 'HERE normalDay',
        'HERE.normalDayCustom' => 'HERE normalDayCustom',
        'HERE.normalDayGrey' => 'HERE normalDayGrey',
        'HERE.normalDayMobile' => 'HERE normalDayMobile',
        'HERE.normalDayGreyMobile' => 'HERE normalDayGreyMobile',
        'HERE.normalDayTransit' => 'HERE normalDayTransit',
        'HERE.normalDayTransitMobile' => 'HERE normalDayTransitMobile',
        'HERE.normalNight' => 'HERE normalNight',
        'HERE.normalNightMobile' => 'HERE normalNightMobile',
        'HERE.normalNightGrey' => 'HERE normalNightGrey',
        'HERE.normalNightGreyMobile' => 'HERE normalNightGreyMobile',
        'HERE.normalNightTransit' => 'HERE normalNightTransit',
        'HERE.normalNightTransitMobile' => 'HERE normalNightTransitMobile',
        'HERE.redcuedDay' => 'HERE redcuedDay',
        'HERE.redcuedNight' => 'HERE redcuedNight',
        'HERE.basicMap' => 'HERE basicMap',
        'HERE.mapLabels' => 'HERE mapLabels',
        'HERE.trafficFlow' => 'HERE trafficFlow',
        'HERE.carnavDayGrey' => 'HERE carnavDayGrey',
        'HERE.hybridDayMobile' => 'HERE hybridDayMobile',
        'HERE.hybridDayTransit' => 'HERE hybridDayTransit',
        'HERE.hybridDayGrey' => 'HERE hybridDayGrey',
        'HERE.pedestrianDay' => 'HERE pedestrianDay',
        'HERE.pedestrianNight' => 'HERE pedestrianNight',
        'HERE.satelliteDay' => 'HERE satelliteDay',
        'HERE.terrainDay' => 'HERE terrainDay',
        'HERE.terrainDayMobile' => 'HERE terrainDayMobile',
      ],
      'FreeMapSK' => [
        'FreeMapSK' => 'FreeMapSK',
      ],
      'MtbMap' => [
        'MtbMap' => 'MtbMap',
      ],
      'CartoDB' => [
        'CartoDB.Positron' => 'CartoDB Positron',
        'CartoDB.PositronNoLabels' => 'CartoDB PositronNoLabels',
        'CartoDB.PositronOnlyLabels' => 'CartoDB PositronOnlyLabels',
        'CartoDB.DarkMatter' => 'CartoDB DarkMatter',
        'CartoDB.DarkMatterNoLabels' => 'CartoDB DarkMatterNoLabels',
        'CartoDB.DarkMatterOnlyLabels' => 'CartoDB DarkMatterOnlyLabels',
        'CartoDB.Voyager' => 'CartoDB Voyager',
        'CartoDB.VoyagerNoLabels' => 'CartoDB VoyagerNoLabels',
        'CartoDB.VoyagerOnlyLabels' => 'CartoDB VoyagerOnlyLabels',
        'CartoDB.VoyagerLabelsUnder' => 'CartoDB VoyagerLabelsUnder',
      ],
      'HikeBike' => [
        'HikeBike' => 'HikeBike',
        'HikeBike.HillShading' => 'HikeBike HillShading',
      ],
      'BasemapAT' => [
        'BasemapAT.basemap' => 'BasemapAT basemap',
        'BasemapAT.grau' => 'BasemapAT grau',
        'BasemapAT.overlay' => 'BasemapAT overlay',
        'BasemapAT.highdpi' => 'BasemapAT highdpi',
        'BasemapAT.orthofoto' => 'BasemapAT orthofoto',
      ],
      'NLS' => [
        'NLS' => 'NLS',
      ],
      'Wikimedia' => [
        'Wikimedia' => 'Wikimedia',
      ],
      'GeoportailFrance' => [
        'GeoportailFrance.parcels' => 'GeoportailFrance parcels',
        'GeoportailFrance.ignMaps' => 'GeoportailFrance ignMaps',
        'GeoportailFrance.maps' => 'GeoportailFrance maps',
        'GeoportailFrance.orthos' => 'GeoportailFrance orthos',
      ],
    ];
  }

}
