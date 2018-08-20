<?php

namespace Drupal\geolocation\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\LocationInputManager;
use Drupal\views\Plugin\views\filter\NumericFilter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\geolocation\ProximityTrait;

/**
 * Filter handler for search keywords.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("geolocation_filter_proximity")
 */
class ProximityFilter extends NumericFilter implements ContainerFactoryPluginInterface {

  use ProximityTrait;

  /**
   * Proximity center manager.
   *
   * @var \Drupal\geolocation\LocationInputManager
   */
  protected $locationInputManager;

  /**
   * Constructs a Handler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\geolocation\LocationInputManager $location_input_manager
   *   Proximity center manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocationInputManager $location_input_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->locationInputManager = $location_input_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.geolocation.locationinput')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    // Add source, lat, lng and filter.
    $options = parent::defineOptions();

    $options['location_input'] = ['default' => []];
    $options['unit'] = ['default' => 'km'];

    $options['value']['contains']['center'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['unit'] = [
      '#title' => $this->t('Distance unit'),
      '#description' => $this->t('Unit to use for conversion of input value to proximity distance.'),
      '#type' => 'select',
      '#default_value' => $this->options['unit'],
      '#weight' => 6,
      '#options' => [
        'mi' => $this->t('Miles'),
        'km' => $this->t('Kilometers'),
      ],
    ];

    $form['location_input'] = $this->locationInputManager->getOptionsForm($this->options['location_input'], $this);
  }

  /**
   * {@inheritdoc}
   */
  public function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if (!isset($form['value']['value'])) {
      $form['value'] = [
        'value' => $form['value'],
        '#tree' => TRUE,
      ];
      $user_input = $form_state->getUserInput();
      $identifier = $this->options['expose']['identifier'];
      $exposed = $form_state->get('exposed');

      if ($exposed && !isset($user_input[$identifier]['value'])) {
        $user_input[$identifier] = [
          'value' => $this->value['value'],
        ];
        if ($this->value['center']) {
          $user_input[$identifier]['center'] = $this->value['center'];
        }
        $form_state->setUserInput($user_input);
      }
    }

    $form['value']['value'] = array_replace($form['value']['value'], [
      '#type' => 'number',
      '#min' => 0,
      '#step' => 0.1,
      '#title' => $this->t('Distance'),
      '#description' => $this->t('Distance in %unit', ['%unit' => $this->options['unit'] === 'km' ? $this->t('Kilometers') : $this->t('Miles')]),
      '#default_value' => self::convertDistance($form['value']['value']['#default_value'], $this->options['unit'], TRUE),
    ]);

    $center_form = $this->locationInputManager->getForm($this->options['location_input'], $this, empty($this->value['center']) ? NULL : $this->value['center']);
    if (!empty($center_form)) {
      $form['value']['center'] = $center_form;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    $value = $form_state->getValue(['options', 'value', 'value']);
    $distance = self::convertDistance($value, $this->options['unit']);
    $form_state->setValue(['options', 'value', 'value'], $distance);

    parent::valueSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    $accept = parent::acceptExposedInput($input);
    if ($accept) {
      $this->value['value'] = self::convertDistance($this->value['value'], $this->options['unit']);
    }

    return $accept;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table = $this->ensureMyTable();

    $center_form_value = [];
    if (!empty($this->value['center'])) {
      $center_form_value = $this->value['center'];
    }
    $center = $this->locationInputManager->getCoordinates($center_form_value, $this->options['location_input'], $this);

    if (
      empty($center)
      || !is_numeric($center['lat'])
      || !is_numeric($center['lng'])
      || !is_numeric($this->value['value'])
    ) {
      return;
    }

    // Build the query expression.
    $expression = self::getProximityQueryFragment($table, $this->realField, $center['lat'], $center['lng']);

    // Get operator info.
    $info = $this->operators();

    // Make sure a callback exists and add a where expression for the chosen
    // operator.
    if (!empty($info[$this->operator]['method']) && method_exists($this, $info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($expression);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opBetween($expression) {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    if ($this->operator == 'between') {
      $query->addWhereExpression($this->options['group'], $expression . ' BETWEEN ' . $this->value['min'] . ' AND ' . $this->value['max']);
    }
    else {
      $query->addWhereExpression($this->options['group'], $expression . ' NOT BETWEEN ' . $this->value['min'] . ' AND ' . $this->value['max']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opSimple($expression) {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $query->addWhereExpression($this->options['group'], $expression . ' ' . $this->operator . ' ' . $this->value['value']);
  }

  /**
   * {@inheritdoc}
   */
  protected function opEmpty($expression) {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    if ($this->operator == 'empty') {
      $operator = "IS NULL";
    }
    else {
      $operator = "IS NOT NULL";
    }

    $query->addWhereExpression($this->options['group'], $expression . ' ' . $operator);
  }

  /**
   * {@inheritdoc}
   */
  protected function opRegex($expression) {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $query->addWhereExpression($this->options['group'], $expression . ' ~* ' . $this->value['value']);
  }

}
