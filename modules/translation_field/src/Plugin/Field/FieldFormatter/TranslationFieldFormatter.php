<?php
/**
 * @file
 * Contains \Drupal\translation_field\Plugin\Field\FieldFormatter\TranslationFieldFormatter.
 */

namespace Drupal\translation_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Translation_field' formatter.
 *
 * @FieldFormatter(
 *   id = "field_translation_formatter",
 *   module = "translation_field",
 *   label = @Translation("Translation"),
 *   field_types = {
 *     "field_translation"
 *   }
 * )
 */
class TranslationFieldFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $summary[] = t('Displays the translation.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();

    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#type' => 'markup',
        '#markup' => '"' . $item->key . '": "' . $item->value . '"',
      );
    }

    return $element;
  }
}