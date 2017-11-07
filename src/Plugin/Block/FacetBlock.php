<?php

namespace Drupal\facets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposes a facet rendered as a block.
 *
 * @Block(
 *   id = "facet_block",
 *   deriver = "Drupal\facets\Plugin\Block\FacetBlockDeriver"
 * )
 */
class FacetBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // The id saved in the configuration is in the format of
    // base_plugin:facet_id. We're splitting that to get to the facet id.
    $facet_mapping = $this->configuration['id'];
    $facet_id = explode(PluginBase::DERIVATIVE_SEPARATOR, $facet_mapping)[1];

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = $this->facetStorage->load($facet_id);

    // Let the facet_manager build the facets.
    $build = $this->facetManager->build($facet);

    // Add contextual links only when we have results.
    if (!empty($build)) {
      $build['#contextual_links']['facets_facet'] = [
        'route_parameters' => ['facets_facet' => $facet->id()],
      ];
    }

    return $build;
  }

}
