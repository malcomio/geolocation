<?php

namespace Drupal\geolocation\Plugin\Field\FieldWidget;

use Drupal\geolocation\GeolocationCore;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Component\Utility\Html;

/**
 * Map widget base.
 */
abstract class GeolocationMapWidgetBase extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

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
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\geolocation\GeolocationCore $geolocation_core
   *   The entity field manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityFieldManagerInterface $entity_field_manager, GeolocationCore $geolocation_core) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->entityFieldManager = $entity_field_manager;

    if (!empty($this->mapProviderId)) {
      $this->mapProvider = $geolocation_core->getMapProviderManager()->getMapProvider($this->mapProviderId, $this->getSettings());
    }

    if (empty($this->mapProviderSettingsFormId)) {
      $this->mapProviderSettingsFormId = $this->mapProviderId . '_settings';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_field.manager'),
      $container->get('geolocation.core')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function flagErrors(FieldItemListInterface $items, ConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    foreach ($violations as $violation) {
      if ($violation->getMessageTemplate() == 'This value should not be null.') {
        $form_state->setErrorByName($items->getName(), $this->t('No location has been selected yet for required field %field.', ['%field' => $items->getFieldDefinition()->getLabel()]));
      }
    }
    parent::flagErrors($items, $violations, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'default_longitude' => NULL,
      'default_latitude' => NULL,
      'auto_client_location' => FALSE,
      'auto_client_location_marker' => FALSE,
      'allow_override_map_settings' => FALSE,
    ];
    $settings += parent::defaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $element = [];

    $element['default_longitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Longitude'),
      '#description' => $this->t('The default center point, before a value is set.'),
      '#default_value' => $settings['default_longitude'],
    ];

    $element['default_latitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Latitude'),
      '#description' => $this->t('The default center point, before a value is set.'),
      '#default_value' => $settings['default_latitude'],
    ];

    $element['auto_client_location'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically use client location, when no value is set'),
      '#default_value' => $settings['auto_client_location'],
    ];
    $element['auto_client_location_marker'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically set marker to client location as well'),
      '#default_value' => $settings['auto_client_location_marker'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][auto_client_location]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['allow_override_map_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow override the map settings when create/edit an content.'),
      '#default_value' => $settings['allow_override_map_settings'],
    ];

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

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = $this->t('Default center longitude @default_longitude and latitude @default_latitude', [
      '@default_longitude' => $settings['default_longitude'],
      '@default_latitude' => $settings['default_latitude'],
    ]);

    if (!empty($settings['auto_client_location'])) {
      $summary[] = $this->t('Will use client location automatically by default');
      if (!empty($settings['auto_client_location_marker'])) {
        $summary[] = $this->t('Will set client location marker automatically by default');
      }
    }

    if (!empty($settings['allow_override_map_settings'])) {
      $summary[] = $this->t('Users will be allowed to override the map settings for each content.');
    }

    $summary = array_replace_recursive($summary, $this->mapProvider->getSettingsSummary($settings[$this->mapProviderSettingsFormId]));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_field_values = [
      'lat' => '',
      'lng' => '',
    ];

    if (!empty($this->fieldDefinition->getDefaultValueLiteral()[0])) {
      $default_field_values = [
        'lat' => $this->fieldDefinition->getDefaultValueLiteral()[0]['lat'],
        'lng' => $this->fieldDefinition->getDefaultValueLiteral()[0]['lng'],
      ];
    }

    if (!empty($items[$delta]->lat) && !empty($items[$delta]->lng)) {
      $default_field_values = [
        'lat' => $items[$delta]->lat,
        'lng' => $items[$delta]->lng,
      ];
    }

    // Hidden lat,lng input fields.
    $element['latitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Latitude'),
      '#default_value' => $default_field_values['lat'],
      '#attributes' => [
        'class' => [
          'geolocation-map-input-latitude',
        ],
      ],
    ];
    $element['longitude'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Longitude'),
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
        'geolocation-map-input-' . $delta,
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);

    $canvas_id = Html::getUniqueId($this->fieldDefinition->getName());

    $settings = $this->getSettings();

    $element['#attributes']['class'][] = 'canvas-' . $canvas_id;

    // Add the map container.
    $element['map_container'] = [
      '#type' => 'container',
      '#weight' => -10,
      '#attached' => [
        'library' => [
          'geolocation/geolocation.widgets.map',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'widgetSettings' => [
              $canvas_id => [
                'autoClientLocation' => $settings['auto_client_location'] ? TRUE : FALSE,
                'autoClientLocationMarker' => $settings['auto_client_location_marker'] ? TRUE : FALSE,
              ],
            ],
          ],
        ],
      ],
    ];

    $element['map_container']['canvas'] = [
      '#theme' => 'geolocation_map_wrapper',
      '#uniqueid' => $canvas_id,
    ];

    $element['map_container']['canvas']['#attached']['drupalSettings']['geolocation']['maps'][$canvas_id] = [
      'id' => $canvas_id,
    ];

    $locations = [];
    foreach ($items as $delta => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      $location = [
        '#theme' => 'geolocation_map_location',
        '#content' => $delta . ': ' . $item->lat . ' ' . $item->lng,
        '#title' => $delta . ': ' . $item->lat . ' ' . $item->lng,
        '#position' => [
          'lat' => $item->lat,
          'lng' => $item->lng,
        ],
      ];

      $element['map_container']['canvas']['#locations'][] = $location;
    }
    $element['map_container']['canvas']['#locations'] = $locations;

    $element['map_container']['controls'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'geocoder-controls-wrapper-' . $canvas_id,
        'class' => [
          'geocode-controls-wrapper',
        ],
      ],
    ];
    $element['map_container']['controls']['location'] = [
      '#type' => 'textfield',
      '#placeholder' => t('Enter a location'),
      '#attributes' => [
        'class' => [
          'location',
          'form-autocomplete',
        ],
      ],
      '#theme_wrappers' => [],
    ];
    $element['map_container']['controls']['search'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => [
        'class' => [
          'search',
        ],
        'title' => t('Search'),
      ],
    ];
    $element['map_container']['controls']['locate'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => [
        'class' => [
          'locate',
        ],
        'style' => 'display: none;',
        'title' => t('Locate'),
      ],
    ];
    $element['map_container']['controls']['clear'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => [
        'class' => [
          'clear',
          'disabled',
        ],
        'title' => t('Clear'),
      ],
    ];

    if ($settings['allow_override_map_settings']) {
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
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    if (!empty($this->settings['allow_override_map_settings'])) {
      if (!empty($values['google_map_settings'])) {
        $values[0]['data'][$this->mapProviderSettingsFormId] = $values[$this->mapProviderSettingsFormId];
      }
    }

    return $values;
  }

}
