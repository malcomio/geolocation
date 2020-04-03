<?php

namespace Drupal\geolocation_gpx\Plugin\geolocation\DataProvider;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\file\Entity\File;
use Drupal\geolocation\DataProviderBase;
use Drupal\geolocation_gpx\Plugin\Field\FieldType\GeolocationGpxFile;
use Drupal\search_api\Plugin\views\field\SearchApiEntityField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\geolocation\DataProviderInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\views\Plugin\views\field\EntityField;
use phpGPX\phpGPX;

/**
 * Class GeolocationGpxBase.
 *
 * @package Drupal\geolocation_gpx\Plugin\geolocation\DataProvider
 */
abstract class GeolocationGpxBase extends DataProviderBase implements DataProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function isViewsGeoOption(FieldPluginBase $views_field) {
    if (
      $views_field instanceof EntityField
      && $views_field->getPluginId() == 'field'
    ) {
      $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($views_field->getEntityType());
      if (!empty($field_storage_definitions[$views_field->field])) {
        $field_storage_definition = $field_storage_definitions[$views_field->field];

        if ($field_storage_definition->getType() == 'geofield') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromViewsRow(ResultRow $row, FieldPluginBase $views_field = NULL) {
    $positions = [];

    if (!($views_field instanceof SearchApiEntityField)) {
      return [];
    }

    foreach ($views_field->getItems($row) as $item) {
      if (!empty($item['value'])) {
        $pieces = explode(',', $item['value']);
        if (count($pieces) != 2) {
          continue;
        }

        $positions[] = [
          'lat' => $pieces[0],
          'lng' => $pieces[1],
        ];
      }
      elseif (!empty($item['raw'])) {
        /** @var \Drupal\geolocation\Plugin\Field\FieldType\GeolocationItem $geolocation_item */
        $geolocation_item = $item['raw'];
        $positions[] = [
          'lat' => $geolocation_item->get('lat')->getValue(),
          'lng' => $geolocation_item->get('lng')->getValue(),
        ];
      }
    }

    return $positions;
  }

  /**
   * {@inheritdoc}
   */
  public function isFieldGeoOption(FieldDefinitionInterface $fieldDefinition) {
    return ($fieldDefinition->getType() == 'geolocation_gpx_file');
  }

  /**
   * Get GPX file.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $fieldItem
   *   Field item.
   *
   * @return \phpGPX\Models\GpxFile|false
   *   GPX file or false.
   */
  public function getGpxFileFromItem(FieldItemInterface $fieldItem) {
    if ($fieldItem instanceof GeolocationGpxFile) {
      $target_id = $fieldItem->get('target_id')->getValue();
      if (empty($target_id)) {
        return FALSE;
      }

      $file = File::load($target_id);
      if (empty($file)) {
        return FALSE;
      }

      $filename = $file->getFileUri();
      $gpx = new phpGPX();

      $file = $gpx->load($filename);
      if (empty($file)) {
        return FALSE;
      }

      return $file;
    }

    return FALSE;
  }

}
