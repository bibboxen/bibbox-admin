<?php
/**
 * @file
 * Contains the clear printer queue action.
 */

namespace Drupal\bibbox\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Clear printer queue to machine.
 *
 * @Action(
 *   id = "clear_printer_queue",
 *   label = @Translation("Clear printer queue to machine"),
 *   type = "node"
 * )
 */
class ClearPrinterQueue extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!is_null($entity)) {
      \Drupal::service('bibbox.proxy')->clearPrinterQueue($entity->id());
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
