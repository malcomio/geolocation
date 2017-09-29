<?php

namespace Drupal\geolocation_google_maps\Plugin\views\style;

use Drupal\geolocation\Plugin\views\style\CommonMapBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\views\ResultRow;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "maps_common",
 *   title = @Translation("Geolocation Google Maps API - CommonMap"),
 *   help = @Translation("Display geolocations on a common map."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 */
class GoogleCommonMap extends CommonMapBase {

  /**
   * {@inheritdoc}
   */
  public function render() {

    $build = parent::render();

    $build['#attached'] = array_merge_recursive(
      empty($build['#attached']) ? [] : $build['#attached'],
      $this->mapProviderManager
        ->createInstance('google_maps')
        ->attachments(empty($this->options['google_map_settings']) ? [] : $this->options['google_map_settings'], $this->mapId),
      [
        'library' => [
          'geolocation_google_maps/geolocation.commonmap.google',
        ],
      ]
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getLocationsFromRow(ResultRow $row) {
    $locations = parent::getLocationsFromRow($row);

    foreach ($locations as $location) {
      if (
        empty($location['#icon'])
        && !empty($this->options['google_map_settings']['marker_icon_path'])
      ) {
        $icon_token_uri = $this->viewsTokenReplace($this->options['google_map_settings']['marker_icon_path'], $this->rowTokens[$row->index]);
        $icon_token_url = file_create_url($icon_token_uri);

        if ($icon_token_url) {
          $location['#icon'] = $icon_token_url;
        }
        else {
          try {
            $icon_token_url = Url::fromUri($icon_token_uri);
            if ($icon_token_url) {
              $location['#icon'] = $icon_token_url->setAbsolute(TRUE)->toString();
            }
          }
          catch (\Exception $e) {
            // User entered mal-formed URL, but that doesn't matter.
            // We hereby skip it anyway.
          }
        }
      }
    }

    return $locations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    parent::buildOptionsForm($form, $form_state);

    $form['google_map_settings'] = $this
      ->mapProviderManager
      ->createInstance('google_maps')
      ->getSettingsForm(empty($this->options['google_map_settings']) ? [] : $this->options['google_map_settings'], ['style_options', 'google_map_settings']);
  }

}
