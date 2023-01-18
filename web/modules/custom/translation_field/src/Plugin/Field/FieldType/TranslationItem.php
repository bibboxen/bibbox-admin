<?php
/**
* @file
* Contains Drupal\translation_field\Plugin\Field\FieldType\TranslationItem.
*/

namespace Drupal\translation_field\Plugin\Field\FieldType;

use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Provides a field type of translation
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
class TranslationItem extends EntityReferenceItem {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
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
        'target_id' => array(
          'description' => 'The ID of the node.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'target_id' => array('target_id'),
      ),
      'foreign keys' => array(
        'target_id' => array(
          'table' => 'node',
          'columns' => array('target_id' => 'nid'),
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    $value1 = $this->get('key')->getValue();
    $value2 = $this->get('value')->getValue();
    $value3 = $this->get('target_id')->getValue();
    return empty($value1) && empty($value2) && empty($value3);
  }


  /**
   * Determines whether the item holds an unsaved entity.
   *
   * This is notably used for "autocreate" widgets, and more generally to
   * support referencing freshly created entities (they will get saved
   * automatically as the hosting entity gets saved).
   *
   * @return bool
   *   TRUE if the item holds an unsaved entity.
   */
  public function hasNewEntity(): bool {
    return !$this->isEmpty() && $this->target_id === NULL && isset($this->entity) && $this->entity->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    if ($this->hasNewEntity()) {
      // Save the entity if it has not already been saved by some other code.
      if ($this->entity->isNew()) {
        $this->entity->save();
      }
      // Make sure the parent knows we are updating this property so it can
      // react properly.
      $this->target_id = $this->entity->id();
    }
    if (!$this->isEmpty() && $this->target_id === NULL && isset($this->entity)) {
      $this->target_id = $this->entity->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['key'] = DataDefinition::create('string')
      ->setLabel(t('Key'))
      ->setDescription(t('The key of the translation'));
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Value'))
      ->setDescription(t('The translation'));

    $properties['target_id']->setRequired(FALSE);

    return $properties;
  }
}
