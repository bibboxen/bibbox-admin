<?php
/**
 * @file
 * Contains the out of order action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Out of order to machine.
 *
 * @Action(
 *   id = "out_of_order",
 *   label = @Translation("Out of order"),
 *   type = "node"
 * )
 */
class OutOfOrder extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->outOfOrder($entity->id());
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
