<?php

namespace Drupal\geolocation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\GeolocationItemTokenTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Render\Element;

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
  static protected $mapProviderId = FALSE;

  /**
   * Map Provider Settings Form ID.
   *
   * @var string
   */
  static protected $mapProviderSettingsFormId = 'map_settings';

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

    if (!empty(static::$mapProviderId)) {
      $this->mapProvider = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider(static::$mapProviderId, $this->getSettings());
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
    $settings['info_text'] = [
      'value' => '',
      'format' => filter_default_format(),
    ];
    $settings['use_overridden_map_settings'] = FALSE;

    $settings[static::$mapProviderSettingsFormId] = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProviderDefaultSettings(static::$mapProviderId);

    $settings += parent::defaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    $settings = parent::getSettings();

    if (empty($settings[static::$mapProviderSettingsFormId])) {
      $settings[static::$mapProviderSettingsFormId] = [];
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $element = [];

    $element['set_marker'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set map marker'),
      '#default_value' => $settings['set_marker'],
    ];

    $element['title'] = [
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

    $element['info_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Marker info text'),
      '#description' => $this->t('When the marker is clicked, this text will be shown in a popup above it. Leave blank to not display. Token replacement supported.'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][set_marker]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    if (!empty($settings['info_text']['value'])) {
      $element['info_text']['#default_value'] = $settings['info_text']['value'];
    }

    if (!empty($settings['info_text']['format'])) {
      $element['info_text']['#format'] = $settings['info_text']['format'];
    }

    $element['replacement_patterns'] = [
      '#type' => 'details',
      '#title' => 'Replacement patterns',
      '#description' => $this->t('The following replacement patterns are available for the "Info text" and the "Hover title" settings.'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][set_marker]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['replacement_patterns']['token_geolocation'] = $this->getTokenHelp();

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    if (
      $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
      || $cardinality > 1
    ) {
      $element['common_map'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display multiple values on a common map'),
        '#description' => $this->t('By default, each value will be displayed in a separate map. Settings this option displays all values on a common map instead. This settings is only useful on multi-value fields.'),
        '#default_value' => $settings['common_map'],
      ];
    }

    $element['use_overridden_map_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom map settings if provided'),
      '#description' => $this->t('The field map widget optionally allows to define custom map settings to use here.'),
      '#default_value' => $settings['use_overridden_map_settings'],
    ];

    if ($this->mapProvider) {
      $mapProviderSettings = $settings[static::$mapProviderSettingsFormId];
      $element[static::$mapProviderSettingsFormId] = $this->mapProvider->getSettingsForm(
        $mapProviderSettings,
        [
          'fields',
          $this->fieldDefinition->getName(),
          'settings_edit_form',
          'settings',
        ]
      );
    }

    return $element;
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
      if (
        !empty($settings['info_text']['value'])
        && !empty($settings['info_text']['format'])
      ) {
        $summary[] = $this->t('Marker Info Text: @type', [
          '@type' => current(explode(chr(10), wordwrap(check_markup($settings['info_text']['value'], $settings['info_text']['format']), 30))),
        ]);
      }

      if (!empty($settings['common_map'])) {
        $summary[] = $this->t('Common Map Display: Yes');
      }
    }

    $summary = array_replace_recursive($summary, $this->mapProvider->getSettingsSummary($settings[static::$mapProviderSettingsFormId]));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($items->count() == 0) {
      return [];
    }

    $elements = [];

    $settings = $this->getSettings();

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

      $location = [
        '#type' => 'geolocation_map_location',
        '#title' => $title,
        '#disable_marker' => empty($settings['set_marker']) ? TRUE : FALSE,
        '#position' => [
          'lat' => $item->lat,
          'lng' => $item->lng,
        ],
      ];

      if (
        !empty($settings['info_text']['value'])
        && !empty($settings['info_text']['format'])
      ) {
        $location['content'] = [
          '#type' => 'processed_text',
          '#text' => \Drupal::token()->replace(
            $settings['info_text']['value'],
            $token_context,
            [
              'callback' => [$this, 'geolocationItemTokens'],
              'clear' => TRUE,
            ]
          ),
          '#format' => $settings['info_text']['format'],
        ];
      }

      $locations[] = $location;
    }

    $element_pattern = [
      '#type' => 'geolocation_map',
      '#settings' => $settings[static::$mapProviderSettingsFormId],
      '#maptype' => static::$mapProviderId,
      '#centre' => [
        'behavior' => 'fitlocations',
      ],
      '#context' => ['formatter' => $this],
    ];

    if (!empty($settings['common_map'])) {
      $elements = [
        0 => $element_pattern,
      ];
      $elements[0]['#id'] = uniqid("map-");
      foreach ($locations as $delta => $location) {
        $elements[0][$delta] = $location;
      }
    }
    else {
      foreach ($locations as $delta => $location) {
        $elements[$delta] = $element_pattern;
        $elements[$delta]['#id'] = uniqid("map-" . $delta . "-");
        $elements[$delta]['content'] = $location;
      }
    }


    if (
      $settings['use_overridden_map_settings']
      && !empty($items->get(0)->getValue()['data'][static::$mapProviderSettingsFormId])
      && is_array($items->get(0)->getValue()['data'][static::$mapProviderSettingsFormId])
    ) {
      $map_settings = $this->mapProvider->getSettings($items->get(0)->getValue()['data'][static::$mapProviderSettingsFormId]);

      if (!empty($settings['common_map'])) {
        $elements[0]['#settings'] = $map_settings;
      }
      else {
        foreach (Element::children($elements) as $delta => $element) {
          $elements[$delta]['#settings'] = $map_settings;
        }
      }
    }

    return $elements;
  }

}
