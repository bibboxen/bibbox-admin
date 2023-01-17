<?php
/**
 * @file
 * Contains \Drupal\bibbox\Controller\ApiController
 */

namespace Drupal\bibbox\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

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
   * @return JsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function translations($id): JsonResponse {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);

    $translations = \Drupal::service('bibbox.proxy')->getTranslationsForMachine($node);

    return new JsonResponse($translations, 200);
  }

  /**
   * Get private offline decrypt certificate based on the request IP.
   *
   * @param Request $request
   *
   * @return Response
   *   The private certificate
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exposePrivateKey(Request $request): Response {
    $ip = $request->headers->get('x-real-ip', '', []);
    $node = $this->getMachineFromIp(reset($ip));
    $node = reset($node);

    $key = $node->get('field_private_key')->getValue()[0] ?? [];
    if (empty($key)) {
      // Fallback to key defined at the default machine.
      $nid = $node->get('field_default')[0]->getValue()['target_id'];
      $defaultNode = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($nid);

      $key = $defaultNode->get('field_private_key')->getValue()[0];
    }
    $key = $key['value'];

    return new Response($key, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
  }

  /**
   * Access callback to only allow access based request IP.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return AccessResultNeutral|AccessResult|AccessResultAllowed
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function accessPrivateKey(AccountInterface $account): AccessResultNeutral|AccessResult|AccessResultAllowed {
    $ip = \Drupal::request()->headers->get('x-real-ip', '', []);
    $nodes = [];
    if (!empty($ip) && is_array($ip)) {
      $nodes = $this->getMachineFromIp(reset($ip));
    }

    return AccessResult::allowedIf(!empty($nodes));
  }

  /**
   * Get machine node based on IP address.
   *
   * @param string $ip
   *   IP address to look up machine.
   *
   * @return array
   *   Nodes that matches in the database.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getMachineFromIp(string $ip): array {
    return \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'field_ip' => 'https://'.$ip,
      ]);
  }

}
