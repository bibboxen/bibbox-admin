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
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * ApiController.
 */
class ApiController extends ControllerBase {
  /**
   * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
   * keys to arrays rather than overwriting the value in the first array with the duplicate
   * value in the second array, as array_merge does. I.e., with array_merge_recursive,
   * this happens (documented behavior):
   *
   * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('org value', 'new value'));
   *
   * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
   * Matching keys' values in the second array overwrite those in the first array, as is the
   * case with array_merge, i.e.:
   *
   * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('new value'));
   *
   * Parameters are passed by reference, though only for performance reasons. They're not
   * altered by this function.
   *
   * @param array $array1
   * @param array $array2
   * @return array
   * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
   * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
   *
   * Modified to ignore null values in array2.
   */
  function array_merge_recursive_distinct_ignore_nulls(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset ($merged[$key]) && is_array($merged[$key])) {
        $merged[$key] = $this->array_merge_recursive_distinct_ignore_nulls($merged[$key], $value);
      }
      else {
        if (isset($value)) {
          $merged[$key] = $value;
        }
      }
    }

    return $merged;
  }


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

    // Load node.
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    // Get machine array.
    $machine = $this->getMachineArray($id);

    // If the machine exists.
    if ($machine) {
      // Merge with Default if one exists.
      if (count($node->get('field_default'))) {
        // Get default array.
        $default = $this->getMachineArray($node->get('field_default')[0]->getValue()['target_id']);

        // Merge.
        $machine = $this->array_merge_recursive_distinct_ignore_nulls($default, $machine);
      }

      // Get IP
      $ip = $node->get('field_ip')->value;

      // Make sure it is set.
      if (!isset($ip)) {
        throw new HttpException('IP not set');
      }

      // Send request.
      $client = \Drupal::httpClient();
      $client->request('POST', $node->get('field_ip')->value . "/api/config", array('json' => $machine));
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
   * @return array
   *   Machine represented as array.
   */
  private function getMachineArray($nid) {
    $node = \Drupal::entityManager()->getStorage('node')->load($nid);

    // Create machine object.
    $machine = [
      'title' => $node->get('title')->value,
      'email' => $node->get('field_email')->value,
      'fbs' => [
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
      'ui' => [
        'timeout' => [
          'idleTimeout' => $node->get('field_timeout')->value,
          'idleWarn' => $node->get('field_timeout_idle')->value,
        ],
        'binSorting' => [
          'default_bin' => $node->get('field_bin_default')->value ? 'left' : 'right',
          'destinations' => [
            'left' => [
              'id' => 'left',
              'text' => $node->get('field_bin_left_text')->value,
              'background_color' => $node->get('field_bin_left_background_color')->value,
              'color' => $node->get('field_bin_left_color')->value,
            ],
            'right' => [
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
      'notification' => [
        'config' => [
          'default_lang' => $node->get('field_notification_default_lang')->value,
          'date_format' => $node->get('field_notification_date_format')->value,
        ],
        'mailer' => [
          'host' => $node->get('field_notification_mailer_host')->value,
          'port' => $node->get('field_notification_mailer_port')->value,
          'secure' => $node->get('field_notification_mailer_secure')->value ? true : false,
          'from' => $node->get('field_notification_mailer_from')->value,
          'subject' => $node->get('field_notification_mailer_subjec')->value,
        ],
        'header' => [
          'brand' => $node->get('field_notification_header_brand')->value,
          'logo' => $node->get('field_notification_header_logo')->value,
          'color' => $node->get('field_notification_header_color')->value,
        ],
        'footer' => [
          'html' => $node->get('field_notification_footer_html')->value,
          'text' => $node->get('field_notification_footer_text')->value,
        ],
        'library' => [
          'title' => $node->get('field_notification_library_title')->value,
          'name' => $node->get('field_notification_library_name')->value,
          'address' => $node->get('field_notification_library_addre')->value,
          'zipcode' => $node->get('field_notification_library_zip')->value,
          'city' => $node->get('field_notification_library_city')->value,
          'phone' => $node->get('field_notification_library_phone')->value,
        ],
        // Will be attached later.
        'layouts' => [
          'status' => [],
          'checkIn' => [],
          'checkOut' => [],
          'reservations' => [],
        ],
      ],
    ];

    // Attach notification layouts
    foreach ($node->get('field_notification_layouts_statu') as $item) {
      $machine['notification']['layouts']['status'][$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_cout') as $item) {
      $machine['notification']['layouts']['checkOut'][$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_cin') as $item) {
      $machine['notification']['layouts']['checkIn'][$item->value] = true;
    }
    foreach ($node->get('field_notification_layouts_reser') as $item) {
      $machine['notification']['layouts']['reservations'][$item->value] = true;
    }

    // Attach bins to binSorting.
    foreach ($node->get('field_bin_left') as $item) {
      $machine['ui']['binSorting']['bins']["" . $item->value] = 'left';
    }
    foreach ($node->get('field_bin_right') as $item) {
      $machine['ui']['binSorting']['bins']["" . $item->value] = 'right';
    }

    // Attach features.
    foreach ($node->get('field_features') as $ref) {
      $feature = \Drupal::entityManager()->getStorage('node')->load($ref->getValue()['target_id']);
      $machine['ui']['features'][] = [
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
      $machine['ui']['languages'][] = [
        'title' => $language->get('title')->value,
        'text' => $language->get('field_text')->value,
        'langKey' => $language->get('field_language_key')->value,
        'icon' => $language->get('field_icon')->value,
      ];
    }

    return $machine;
  }
}