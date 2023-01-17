<?php
/**
 * @file
 * Contains \Drupal\bibbox\Controller\ApiController
 */

namespace Drupal\bibbox\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiController.
 */
class ApiController extends ControllerBase {
  /**
   * Push config to a machine.
   *
   * @param Request $request
   * @param $id
   *
   * @return RedirectResponse
   */
  public function pushConfig(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->pushConfig($id);

    return new RedirectResponse($destination);
  }

  /**
   * Push translations to machine.
   *
   * @param Request $request
   * @param $id
   *
   * @return RedirectResponse
   */
  public function pushTranslations(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->pushTranslation($id);

    return new RedirectResponse($destination);
  }

  /**
   * Restart UI to a machine.
   *
   * @param Request $request
   * @param $id
   *
   * @return RedirectResponse
   */
  public function restartUI(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->restartUI($id);

    return new RedirectResponse($destination);
  }

  /**
   * Restart Node to a machine.
   *
   * @param Request $request
   * @param $id
   *
   * @return RedirectResponse
   */
  public function restartNode(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->restartNode($id);

    return new RedirectResponse($destination);
  }

  /**
   * Reboot the machine.
   *
   * @param Request $request
   * @param $id
   *
   * @return RedirectResponse
   */
  public function rebootMachine(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->rebootMachine($id);

    return new RedirectResponse($destination);
  }

  /**
   * Out of order.
   *
   * @param Request $request
   * @param $id
   * @return RedirectResponse
   */
  public function outOfOrder(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->outOfOrder($id);

    return new RedirectResponse($destination);
  }

  /**
   * Clear printer queue.
   *
   * @param Request $request
   * @param $id
   * @return RedirectResponse
   */
  public function clearPrinterQueue(Request $request, $id): RedirectResponse {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    \Drupal::service('bibbox.proxy')->clearPrinterQueue($id);

    return new RedirectResponse($destination);
  }

  /**
   * Get json array of machines.
   *
   * @return JsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function machines(): JsonResponse {
    $machines = [];

    // Query for machine ids.
    $query = \Drupal::entityQuery('node')->condition('type', 'machine');
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

      // Add machine to $machines.
      $machines[] = \Drupal::service('bibbox.proxy')->getMachineArray($node);
    }

    return new JsonResponse($machines, 200);
  }

  /**
   * Get json array of machines.
   *
   * @param $id
   *
   * @return JsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function machine($id): JsonResponse {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);

    $machine = \Drupal::service('bibbox.proxy')->getMachineArray($node);

    return new JsonResponse($machine, 200);
  }

  /**
   * Get json array of machines.
   *
   * @param $id
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function translations($id): JsonResponse {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);

    $translations = \Drupal::service('bibbox.proxy')->getTranslationsForMachine($node);

    return new JsonResponse($translations, 200);
  }
}
