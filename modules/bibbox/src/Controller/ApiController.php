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
      'ui' => (object)[
        'timeout' => (object)[
          'idleTimeout' => $node->get('field_timeout')->value,
          'idleWarn' => $node->get('field_timeout_idle')->value,
        ],
        'binSorting' => (object) [
          'default_bin' => $node->get('field_bin_default')->value ? 'left' : 'right',
          'destinations' => (object) [
            'left' => (object) [
              'id' => 'left',
              'text' => $node->get('field_bin_left_text')->value,
              'background_color' => $node->get('field_bin_left_background_color')->value,
              'color' => $node->get('field_bin_left_color')->value,
            ],
            'right' => (object) [
              'id' => 'right',
              'text' => $node->get('field_bin_right_text')->value,
              'background_color' => $node->get('field_bin_right_background_color')->value,
              'color' => $node->get('field_bin_right_color')->value,
            ]
          ],
          // Will be attached later.
          "bins" => []
        ],
        'display_more_materials' => $node->get('field_image_more_materials')->value ? true : false,
        'display_fines' => $node->get('field_display_fines')->value ? true : false,
        'login' => [
          'allow_scan' => $node->get('field_login_allow_scan')->value ? true : false,
          'allow_manual' => $node->get('field_login_allow_manual')->value ? true : false,
        ],
        // Will be attached later.
        'features' => [],
        // Will be attached later.
        'languages' => [],
      ],
      'notification' => (object) [
        'config' => (object)[
          'default_lang' => $node->get('field_notification_default_lang')->value,
          'date_format' => $node->get('field_notification_date_format')->value,
        ],
        'mailer' => (object)[
          'host' => $node->get('field_notification_mailer_host')->value,
          'port' => $node->get('field_notification_mailer_port')->value,
          'secure' => $node->get('field_notification_mailer_secure')->value ? true : false,
          'from' => $node->get('field_notification_mailer_from')->value,
          'subject' => $node->get('field_notification_mailer_subjec')->value,
        ],
        'header' => (object)[
          'brand' => $node->get('field_notification_header_brand')->value,
          'logo' => $node->get('field_notification_header_logo')->value,
          'color' => $node->get('field_notification_header_color')->value,
        ],
        'footer' => (object)[
          'html' => $node->get('field_notification_footer_html')->value,
          'text' => $node->get('field_notification_footer_text')->value,
        ],
        'library' => (object)[
          'title' => $node->get('field_notification_library_title')->value,
          'name' => $node->get('field_notification_library_name')->value,
          'address' => $node->get('field_notification_library_addre')->value,
          'zipcode' => $node->get('field_notification_library_zip')->value,
          'city' => $node->get('field_notification_library_city')->value,
          'phone' => $node->get('field_notification_library_phone')->value,
        ],
        // Will be attached later.
        'layouts' => (object)[
          'status' => [],
          'checkIn' => [],
          'checkOut' => [],
          'reservations' => [],
        ],
      ],
    ];

    // Attach notification layouts
    foreach ($node->get('field_notification_layouts_statu') as $item) {
      $machine->notification->layouts->status[$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_cout') as $item) {
      $machine->notification->layouts->checkOut[$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_cin') as $item) {
      $machine->notification->layouts->checkIn[$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_reser') as $item) {
      $machine->notification->layouts->reservations[$item->value] = true;
    }

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