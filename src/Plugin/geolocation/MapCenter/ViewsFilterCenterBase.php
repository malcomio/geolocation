<?php

namespace Drupal\geolocation\Plugin\geolocation\MapCenter;

use Drupal\geolocation\MapCenterInterface;
use Drupal\geolocation\MapCenterBase;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Views filter based center base class.
 */
abstract class ViewsFilterCenterBase extends MapCenterBase implements MapCenterInterface {

  protected $viewsFilterPluginId = '';

  /**
   * {@inheritdoc}
   */
  public function getAvailableMapCenterOptions(array $context) {
    $options = [];

    if (
      !empty($context['views_style'])
      && is_a($context['views_style'], StylePluginBase::class)
    ) {
      /** @var \Drupal\views\Plugin\views\style\StylePluginBase $views_style */
      $views_style = $context['views_style'];
      $filters = $views_style->displayHandler->getOption('filters');
      foreach ($filters as $filter_id => $filter) {
        if ($filter['plugin_id'] == $this->viewsFilterPluginId) {
          /** @var \Drupal\views\Plugin\views\filter\FilterPluginBase $filter_handler */
          $filter_handler = $views_style->displayHandler->getHandler('filter', $filter_id);

          $options[$filter_id] = $filter_handler->adminLabel();
        }
      }
    }

    return $options;
  }

}
