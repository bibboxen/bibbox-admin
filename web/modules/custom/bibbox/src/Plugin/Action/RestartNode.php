<?php
/**
 * @file
 * Contains the restart node action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Restart node to machine.
 *
 * @Action(
 *   id = "restart_node",
 *   label = @Translation("Restart node to machine"),
 *   type = "node"
 * )
 */
class RestartNode extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->restartNode($entity->id());
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
