<?php
/**
 * @file
 * Contains \Drupal\translation_field\Plugin\Field\FieldFormatter\TranslationFieldFormatter.
 */

namespace Drupal\translation_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Annotation\FieldFormatter;
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
  public function settingsSummary(): array {
    $summary = array();
    $summary[] = t('Displays the translation.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = array();

    foreach ($items as $delta => $item) {
      $itemTitle = $item->entity ? $item->entity->title->value : NULL;

      $element[$delta] = array(
        '#type' => 'markup',
        '#markup' =>
          '<td class="translation-field--key">' .
          $item->key .
          '</td><td class="translation-field--value">' .
          $item->value .
          '</td><td class="translation-field--machine">' .
          $itemTitle .
          '</td>',
      );
    }

    return $element;
  }
}
