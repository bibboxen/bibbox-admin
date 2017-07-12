<?php
/**
 * @file
 * Contains the push translation action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Push translation to machine.
 *
 * @Action(
 *   id = "push_translation",
 *   label = @Translation("Push translation to machine"),
 *   type = "node"
 * )
 */
class PushTranslation extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->pushTranslation($entity->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

}
