<?php
/**
* @file
* Contains Drupal\translation_field\Plugin\Field\FieldType\TranslationItem.
*/

namespace Drupal\translation_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of a translation
 *
 * @FieldType(
 *   id = "translation",
 *   label = @Translation("Translation"),
 *   module = "translation_field",
 *   description = @Translation("Provides a translation field"),
 *   default_widget = "field_translation_widget",
 *   default_formatter = "field_translation_formatter"
 * )
 */
class TranslationItem extends FieldItemBase implements FieldItemInterface {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'key' => array(
          'type' => 'text',
          'size' => 'tiny',
          'not null' => TRUE,
        ),
        'value' => array(
          'type' => 'text',
          'size' => 'tiny',
          'not null' => TRUE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value1 = $this->get('key')->getValue();
    $value2 = $this->get('value')->getValue();
    return empty($value1) && empty($value2);
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['key'] = DataDefinition::create('string')
      ->setLabel(t('Key'))
      ->setDescription(t('The key of the translation'));
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The translation'));

    return $properties;
  }

}