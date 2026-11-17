<?php

namespace Drupal\translation_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_translation' widget.
 *
 * @FieldWidget(
 *   id = "field_translation_widget",
 *   label = @Translation("Translation"),
 *   field_types = {
 *     "field_translation"
 *   }
 * )
 */
class TranslationWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $element['#prefix'] = '<div class="translation-field-wrapper">';
    $element['#suffix'] = '</div>';

    $element['key'] = [
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#default_value' => $items[$delta]->key ?? '',
      '#size' => 25,
      '#attributes' => ['class' => ['translation-field-key']],
    ];
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => t('Value'),
      '#default_value' => $items[$delta]->value ?? '',
      '#size' => 25,
      '#attributes' => ['class' => ['translation-field-value']],
    ];
    $element['target_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Machine'),
      '#target_type' => 'node',
      '#default_value' => $items[$delta]->entity ?? NULL,
      '#selection_settings' => [
        'target_bundles' => ['machine'],
      ],
      '#size' => 25,
      '#weight'   => 5,
      '#settings' => [
        'match_operator'    => 'CONTAINS',
        'size'              => '60',
        'autocomplete_type' => 'node',
      ],
      '#required' => FALSE,
    ];

    // If cardinality is 1, ensure a label is output for the field by wrapping
    // it in a details element.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += [
        '#type' => 'fieldset',
        '#attributes' => ['class' => ['container-inline']],
      ];
    }

    return $element;
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) == 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (!preg_match('/^#([a-f0-9]{6})$/iD', strtolower($value))) {
      $form_state->setError($element, t('Color must be a 6-digit hexadecimal value, suitable for CSS.'));
    }
  }

}
