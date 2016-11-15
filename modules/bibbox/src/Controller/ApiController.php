<?php
/**
 * @file
 * Contains \Drupal\bibbox\Controller\ApiController
 */

namespace Drupal\bibbox\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ApiController.
 */
class ApiController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function machines() {
    $machines = [];
    $languages = [];

    // Attach languages.
    $query = \Drupal::entityQuery('node')->condition('type', 'language');
    $nids = $query->execute();


    $languageNodes = \Drupal::entityManager()->getStorage('node')->loadMultiple($nids);
    foreach ($languageNodes as $language) {
      $languages[] = (object) [
        'text' => $language->get('field_text')->value,
        'langKey' => $language->get('field_language_key')->value,
        'icon' => $language->get('field_icon')->value,
      ];
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'machine');
    $nids = $query->execute();

    // Load nodes and build response.
    $nodes = \Drupal::entityManager()->getStorage('node')->loadMultiple($nids);
    foreach ($nodes as $node) {
      $machine = (object) [
        'title' => $node->get('title')->value,
        'name' => $node->get('field_name')->value,
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
          'features' => [],
          'languages' => $languages,
        ],
      ];

      // Attach bin sorting.
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
          'icon' => $feature->get('field_icon')->value,
          'require_offline' => boolval($feature->get('field_require_online')->value),
          'text' => $feature->get('field_text')->value,
          'url' => $feature->get('field_url')->value,
        ];
      }

      $machines[] = $machine;
    }

    return new JsonResponse($machines, 200);
  }
}