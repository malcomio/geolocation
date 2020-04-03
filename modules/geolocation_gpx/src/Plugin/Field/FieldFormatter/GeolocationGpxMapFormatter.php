<?php

namespace Drupal\geolocation_gpx\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geofield' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_gpx_file",
 *   module = "geolocation",
 *   label = @Translation("Geolocation GPX Formatter - Map"),
 *   field_types = {
 *     "geolocation_gpx_file"
 *   }
 * )
 */
class GeolocationGpxMapFormatter extends GeolocationMapFormatterBase {

  /**
   * Track data provider.
   *
   * @var \Drupal\geolocation_gpx\Plugin\geolocation\DataProvider\GeolocationGpxTracks
   */
  protected $dataProvider;

  /**
   * {@inheritdoc}
   */
  static protected $dataProviderId = 'geolocation_gpx_tracks';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['show_tracks'] = TRUE;
    $settings['show_waypoints'] = TRUE;
    $settings['track_stroke_color'] = '#FF0044';
    $settings['track_stroke_color_randomize'] = TRUE;
    $settings['track_stroke_width'] = 2;
    $settings['track_stroke_opacity'] = 1;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    unset($element['set_marker']);
    // @TODO: re-enable?
    unset($element['title']);
    unset($element['info_text']);

    $settings = $this->getSettings();

    $element['show_waypoints'] = [
      '#weight' => -100,
      '#type' => 'checkbox',
      '#title' => $this->t('Show waypoints'),
      '#description' => $this->t('Will be displayed as regular markers, with the name as marker title.'),
      '#default_value' => $settings['show_waypoints'],
    ];

    $element['show_tracks'] = [
      '#weight' => -99,
      '#type' => 'checkbox',
      '#title' => $this->t('Show tracks'),
      '#description' => $this->t('Will be displayed as polylines; names should show up on hover/click.'),
      '#default_value' => $settings['show_tracks'],
    ];

    $element['track_stroke_color'] = [
      '#weight' => -98,
      '#type' => 'color',
      '#title' => $this->t('Track color'),
      '#default_value' => $settings['track_stroke_color'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_tracks]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['track_stroke_color_randomize'] = [
      '#weight' => -98,
      '#type' => 'checkbox',
      '#title' => $this->t('Randomize track colors'),
      '#default_value' => $settings['track_stroke_color_randomize'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_tracks]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['track_stroke_width'] = [
      '#weight' => -98,
      '#type' => 'number',
      '#title' => $this->t('Track Width'),
      '#description' => $this->t('Width of the tracks in pixels.'),
      '#default_value' => $settings['track_stroke_width'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_tracks]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['track_stroke_opacity'] = [
      '#weight' => -98,
      '#type' => 'number',
      '#step' => 0.01,
      '#title' => $this->t('Track Opacity'),
      '#description' => $this->t('Opacity of the tracks from 1 = fully visible, 0 = complete see through.'),
      '#default_value' => $settings['track_stroke_opacity'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_tracks]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getLocations(FieldItemListInterface $items) {

    $settings = $this->getSettings();

    $locations = [];

    foreach ($items as $delta => $item) {
      $gpxFile = $this->dataProvider->getGpxFileFromItem($item);

      if (empty($gpxFile)) {
        continue;
      }

      if ($settings['show_tracks']) {
        foreach ($gpxFile->tracks as $track) {
          $coordinates = '';
          foreach ($track->segments as $segment) {
            foreach ($segment->points as $point) {
              $coordinates .= $point->latitude . ',' . $point->longitude . ' ';
            }
          }
          $location = [
            '#type' => 'geolocation_map_polyline',
            '#title' => $track->name,
            '#coordinates' => $coordinates,
            '#weight' => $delta,
            '#stroke_color' => $settings['track_stroke_color_randomize'] ? sprintf('#%06X', mt_rand(0, 0xFFFFFF)) : $settings['track_stroke_color'],
            '#stroke_width' => (int) $settings['track_stroke_width'],
            '#stroke_opacity' => (float) $settings['track_stroke_opacity'],
          ];

          $locations[] = $location;
        }
      }

      if ($settings['show_waypoints']) {
        foreach ($gpxFile->waypoints as $waypoint) {
          if (empty($waypoint)) {
            continue;
          }

          $location = [
            '#type' => 'geolocation_map_location',
            '#title' => $waypoint->name,
            '#disable_marker' => empty($settings['set_marker']) ? TRUE : FALSE,
            '#coordinates' => [
              'lat' => $waypoint->latitude,
              'lng' => $waypoint->longitude,
            ],
            '#weight' => $delta,
          ];

          $locations[] = $location;
        }
      }
    }

    return $locations;
  }

}
