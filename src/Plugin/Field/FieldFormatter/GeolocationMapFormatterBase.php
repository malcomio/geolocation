<?php

namespace Drupal\geolocation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\GeolocationItemTokenTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin base for Map based formatters.
 */
abstract class GeolocationMapFormatterBase extends FormatterBase {

  use GeolocationItemTokenTrait;

  /**
   * Map Provider ID.
   *
   * @var string
   */
  protected $mapProviderId = FALSE;

  /**
   * Map Provider Settings Form ID.
   *
   * @var string
   */
  protected $mapProviderSettingsFormId = FALSE;

  /**
   * Map Provider.
   *
   * @var \Drupal\geolocation\MapProviderInterface
   */
  protected $mapProvider = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    if (!empty($this->mapProviderId)) {
      $this->mapProvider = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider($this->mapProviderId, $this->getSettings());
    }

    if (empty($this->mapProviderSettingsFormId)) {
      $this->mapProviderSettingsFormId = $this->mapProviderId . '_settings';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];
    $settings['title'] = '';
    $settings['set_marker'] = TRUE;
    $settings['common_map'] = TRUE;
    $settings['info_text'] = '';
    $settings += parent::defaultSettings();
    $settings['use_overridden_map_settings'] = FALSE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $form['set_marker'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set map marker'),
      '#description' => $this->t('The map will be centered on the stored location. Additionally a marker can be set at the exact location.'),
      '#default_value' => $settings['set_marker'],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Marker title'),
      '#description' => $this->t('When the cursor hovers on the marker, this title will be shown as description.'),
      '#default_value' => $settings['title'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][set_marker]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['info_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Marker info text'),
      '#description' => $this->t('When the marker is clicked, this text will be shown in a popup above it. Leave blank to not display. Token replacement supported.'),
      '#default_value' => $settings['info_text'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][set_marker]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['replacement_patterns'] = [
      '#type' => 'details',
      '#title' => 'Replacement patterns',
      '#description' => $this->t('The following replacement patterns are available for the "Info text" and the "Hover title" settings.'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][set_marker]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['replacement_patterns']['token_geolocation'] = $this->getTokenHelp();

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (
      $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
      || $cardinality > 1
    ) {
      $form['common_map'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display multiple values on a common map'),
        '#description' => $this->t('By default, each value will be displayed in a separate map. Settings this option displays all values on a common map instead. This settings is only useful on multi-value fields.'),
        '#default_value' => $settings['common_map'],
      ];
    }

    if ($this->mapProvider) {
      $mapProviderSettings = empty($settings[$this->mapProviderSettingsFormId]) ? [] : $settings[$this->mapProviderSettingsFormId];
      $form[$this->mapProviderSettingsFormId] = $this->mapProvider->getSettingsForm(
        $mapProviderSettings,
        [
          'fields',
          $this->fieldDefinition->getName(),
          'settings_edit_form',
          'settings',
        ]
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary = [];
    $summary[] = $this->t('Marker set: @marker', ['@marker' => $settings['set_marker'] ? $this->t('Yes') : $this->t('No')]);
    if ($settings['set_marker']) {
      $summary[] = $this->t('Marker Title: @type', ['@type' => $settings['title']]);
      $summary[] = $this->t('Marker Info Text: @type', [
        '@type' => current(explode(chr(10), wordwrap($settings['info_text'], 30))),
      ]);
      if (!empty($settings['common_map'])) {
        $summary[] = $this->t('Common Map Display: Yes');
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($items->count() == 0) {
      return [];
    }

    $settings = $this->getSettings();

    $elements = [
      '#attached' => [
        'library' => [
          'geolocation/geolocation.map',
        ],
      ],
    ];

    $token_context = [
      $this->fieldDefinition->getTargetEntityTypeId() => $items->getEntity(),
    ];

    $locations = [];

    foreach ($items as $delta => $item) {
      $token_context['geolocation_current_item'] = $item;

      $title = \Drupal::token()->replace($settings['title'], $token_context, [
        'callback' => [$this, 'geolocationItemTokens'],
        'clear' => TRUE,
      ]);
      if (empty($title)) {
        $title = $item->lat . ', ' . $item->lng;
      }
      $content = \Drupal::token()
        ->replace($settings['info_text'], $token_context, [
          'callback' => [$this, 'geolocationItemTokens'],
          'clear' => TRUE,
        ]);

      $location = [
        '#type' => 'geolocation_map_location',
        'content' => [
          '#markup' => $content,
        ],
        '#title' => $title,
        '#disable_marker' => empty($settings['set_marker']) ? TRUE : FALSE,
        '#position' => [
          'lat' => $item->lat,
          'lng' => $item->lng,
        ],
      ];

      /*
       * Add location to later display one common map.
       */
      if (!empty($settings['common_map'])) {
        $locations[] = $location;
      }

      /*
       * Add each single map to render output.
       */
      else {
        $id = uniqid("map-");

        $elements[$delta] = [
          '#type' => 'geolocation_map',
        ];
        $elements[$delta]['location'] = $location;
        $elements[$delta]['#id'] = $id;
        $elements['#attached']['drupalSettings']['geolocation']['maps'][$id] = [
          'id' => $id,
        ];
        $elements[$delta]['#centre'] = $location['#position'];
      }
    }

    /*
     * Display one map with all locations.
     */
    if (!empty($settings['common_map'])) {
      $id = uniqid("map-");

      $elements = [
        '#type' => 'geolocation_map',
      ];
      foreach ($locations as $delta => $location) {
        $elements['location_' . $delta] = $location;
      }
      $elements['#id'] = $id;
      $elements['#attached']['drupalSettings']['geolocation']['maps'][$id] = [
        'id' => $id,
      ];

      if (empty($locations)) {
        return [];
      }
      else {
        $elements['#centre'] = [
          'lat' => $locations[0]['#position']['lat'],
          'lng' => $locations[0]['#position']['lng'],
        ];
      }
    }

    return $elements;
  }

}
