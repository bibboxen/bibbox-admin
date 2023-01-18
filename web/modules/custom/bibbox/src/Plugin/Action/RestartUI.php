<?php
/**
 * @file
 * Contains the restart ui action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Annotation\Action;
use Drupal\Core\Session\AccountInterface;

/**
 * Restart ui to machine.
 *
 * @Action(
 *   id = "restart_ui",
 *   label = @Translation("Restart ui to machine"),
 *   type = "node"
 * )
 */
class RestartUI extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->restartUI($entity->id());
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
