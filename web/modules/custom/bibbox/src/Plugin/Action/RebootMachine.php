<?php
/**
 * @file
 * Contains the reboot machine action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Reboot machine.
 *
 * @Action(
 *   id = "reboot_machine",
 *   label = @Translation("Reboot machine"),
 *   type = "node"
 * )
 */
class RebootMachine extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->rebootMachine($entity->id());
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
