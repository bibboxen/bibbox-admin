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
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * ApiController.
 */
class ApiController extends ControllerBase {
  /**
   * Push config to a machine.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function pushConfig(Request $request, $id) {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    $machine = $this->getMachineArray($id);

    if ($machine) {
      // @TODO: Do the stuff!
    }

    return new RedirectResponse($destination);
  }

  /**
   * Push config to a machine.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function restartUI(Request $request, $id) {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    // @TODO: Do the stuff!

    return new RedirectResponse($destination);
  }

  /**
   * Clone a machine node.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function cloneMachine(Request $request, $id) {
    // Get destination to return to after completing request.
    $destination = $request->query->get('destination');

    $node = \Drupal::entityManager()->getStorage('node')->load($id);
/*
    $clone = Node::create([
      'type'        => 'machine',
      'title'       => '',
    ]);
    $clone->save();
*/
    return new RedirectResponse($destination);
  }

  /**
   * Get json array of machines.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function machines() {
    $machines = [];

    // Query for machine ids.
    $query = \Drupal::entityQuery('node')->condition('type', 'machine');
    $nids = $query->execute();

    foreach ($nids as $nid) {
      // Add machine to $machines.
      $machines[] = $this->getMachineArray($nid);
    }

    return new JsonResponse($machines, 200);
  }

  /**
   * Get an array representation of a machine.
   *
   * @param $nid
   *   Node id.
   *
   * @return object
   *   Machine represented as object.
   */
  private function getMachineArray($nid) {
    $node = \Drupal::entityManager()->getStorage('node')->load($nid);

    // Create machine object.
    $machine = (object) [
      'title' => $node->get('title')->value,
      'email' => $node->get('field_email')->value,
      'ui' => (object)[
        'timeout' => (object)[
          'idleTimeout' => $node->get('field_timeout')->value,
          'idleWarn' => $node->get('field_timeout_idle')->value,
        ],
        'fbs' => (object)[
          'username' => $node->get('field_fbs_username')->value,
          'password' => $node->get('field_fbs_password')->value,
          'endpoint' => $node->get('field_fbs_endpoint')->value,
          'agency' => $node->get('field_fbs_agency')->value,
          'location' => $node->get('field_fbs_location')->value,
          'loginAttempts' => (object) [
            'max' => $node->get('field_fbs_login_attempts_max')->value,
            'timeLimit' => $node->get('field_fbs_login_attempts_time_li')->value,
          ],
        ],
        'binSorting' => (object) [
          'default_bin' => $node->get('field_bin_default')->value ? 'left' : 'right',
          'destinations' => (object) [
            'left' => (object) [
              'id' => 'left',
              'text' => $node->get('field_bin_left_text')->value
            ],
            'right' => (object) [
              'id' => 'right',
              'text' => $node->get('field_bin_right_text')->value,
              'background_color' => '#2d66a6',
              'color' => '#fff',
            ]
          ],
          "bins" => []
        ],
        'display_more_materials' => $node->get('field_image_more_materials')->value ? true : false,
        'display_bills' => $node->get('field_display_bills')->value ? true : false,
        'login' => [
          'allow_scan' => $node->get('field_login_allow_scan')->value ? true : false,
          'allow_manual' => $node->get('field_login_allow_manual')->value ? true : false,
        ],
        'features' => [],
        'languages' => [],
      ],
    ];

    // Attach bins to binSorting.
    foreach ($node->get('field_bin_left') as $item) {
      $machine->ui->binSorting->bins["" . $item->value] = 'left';
    }
    foreach ($node->get('field_bin_right') as $item) {
      $machine->ui->binSorting->bins["" . $item->value] = 'right';
    }
    $machine->ui->binSorting->bins = (object) $machine->ui->binSorting->bins;

    // Attach features.
    foreach ($node->get('field_features') as $ref) {
      $feature = \Drupal::entityManager()->getStorage('node')->load($ref->getValue()['target_id']);
      $machine->ui->features[] = (object)[
        'title' => $feature->get('title')->value,
        'icon' => $feature->get('field_icon')->value,
        'require_offline' => boolval($feature->get('field_require_online')->value),
        'text' => $feature->get('field_text')->value,
        'url' => $feature->get('field_url')->value,
      ];
    }

    // Attach languages.
    foreach ($node->get('field_languages') as $ref) {
      $language = \Drupal::entityManager()->getStorage('node')->load($ref->getValue()['target_id']);
      $machine->ui->languages[] = (object)[
        'title' => $language->get('title')->value,
        'text' => $language->get('field_text')->value,
        'langKey' => $language->get('field_language_key')->value,
        'icon' => $language->get('field_icon')->value,
      ];
    }

    return $machine;
  }
}