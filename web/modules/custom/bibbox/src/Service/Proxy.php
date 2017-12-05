<?php
/**
 * @file
 * Contains proxy to machines.
 */

namespace Drupal\bibbox\Service;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class Proxy {
  private $client = NULL;

  /**
   * Default construct.
   *
   * Load koba configuration.
   * @param Client $client
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * Push config to machine with $id.
   *
   * @param $id
   * @throws \HttpException
   */
  public function pushConfig($id) {
    // Load node.
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    // Get machine array.
    $machine = $this->getMachineArray($node);

    // If the machine exists.
    if ($machine) {
      // Merge with Default if one exists.
      if (count($node->get('field_default'))) {
        // Get default node.
        $defaultNode = \Drupal::entityManager()
          ->getStorage('node')
          ->load($node->get('field_default')[0]->getValue()['target_id']);

        // Get default array.
        $default = $this->getMachineArray($defaultNode);

        // Merge.
        $machine = $this->array_merge_recursive_distinct_ignore_nulls($default, $machine);
      }

      // Get IP
      $ip = $node->get('field_ip')->value;

      // Make sure it is set.
      if (!isset($ip)) {
        throw new \HttpException('IP not set');
      }

      try {
        $this->client->request(
          'POST',
          $node->get('field_ip')->value . "/config",
          array(
            'json' => $machine,
            'verify' => false
          )
        );
      } catch (RequestException $e) {
        drupal_set_message('Error pushing config to "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
        \Drupal::logger('bibbox')->error($e);
      }
    }
  }

  /**
   * Push translation to machine with $id.
   *
   * @param $id
   * @throws \HttpException
   */
  public function pushTranslation($id) {
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    $translations = $this->getTranslationsForMachine($node);

    $ip = $node->get('field_ip')->value;

    // Make sure it is set.
    if (!isset($ip)) {
      throw new \HttpException('IP not set');
    }

    try {
      $this->client->request(
        'POST',
        $ip . "/translations",
        array(
          'json' => $translations,
          'verify' => false
        )
      );
    } catch (RequestException $e) {
      drupal_set_message('Error pushing translations to "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
      \Drupal::logger('bibbox')->error($e);
    }
  }

  /**
   * Restart the UI of the machine with $id.
   *
   * @param $id
   */
  public function restartUI($id) {
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    $ip = $node->get('field_ip')->value;

    try {
      $this->client->request('POST', $ip . "/restart/ui", array('verify' => false));
    } catch (RequestException $e) {
      drupal_set_message('Error restarting UI of "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
      \Drupal::logger('bibbox')->error($e);
    }
  }

  /**
   * Restart the node of the machine with $id.
   *
   * @param $id
   */
  public function restartNode($id) {
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    $ip = $node->get('field_ip')->value;

    try {
      $this->client->request('POST', $ip . "/restart/application", array('verify' => false));
    } catch (RequestException $e) {
      drupal_set_message('Error restarting Node of "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
      \Drupal::logger('bibbox')->error($e);
    }
  }

  /**
   * Reboot the machine with $id.
   *
   * @param $id
   */
  public function rebootMachine($id) {
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    $ip = $node->get('field_ip')->value;

    try {
      $this->client->request('POST', $ip . "/reboot", array('verify' => false));
    } catch (RequestException $e) {
      drupal_set_message('Error rebooting machine of "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
      \Drupal::logger('bibbox')->error($e);
    }
  }

  /**
   * Set the machine with $id out of order.
   *
   * @param $id
   */
  public function outOfOrder($id) {
    $node = \Drupal::entityManager()->getStorage('node')->load($id);

    $ip = $node->get('field_ip')->value;

    try {
      $this->client->request('POST', $ip . "/outoforder", array('verify' => false));
    } catch (RequestException $e) {
      drupal_set_message('Error setting out of order of "' . $node->get('title')->value . '" ( ' . $ip . ' )', 'error');
      \Drupal::logger('bibbox')->error($e);
    }
  }

  /**
   * Get translations for machine.
   *
   * @param $node
   *   The machine node.
   * @return array
   *   The translations for the node.
   *   This is the result of merging:
   *     1. All translations without a machine attached.
   *     2. All translations with $node.field_default machine attached.
   *     3. All translations with $node machine attached.
   */
  public function getTranslationsForMachine($node) {
    $baseTranslations = $this->getTranslationsArray(NULL);

    $defaults = [];
    // Merge with Default if one exists.
    if (count($node->get('field_default'))) {
      $defaults = $this->getTranslationsArray($node->get('field_default')[0]->getValue()['target_id']);
    }

    $defaults = $this->array_merge_recursive_distinct_ignore_nulls($baseTranslations, $defaults);

    return $this->array_merge_recursive_distinct_ignore_nulls($defaults, $this->getTranslationsArray($node->id()));
  }

  /**
   * Get an array representation of all translations.
   *
   * @param $mid
   *   Optionally. Get only the translations where Machine.id == $mid.
   *
   * @return array
   */
  public function getTranslationsArray($mid) {
    $translations = [
      'ui' => [],
      'notification' => [],
    ];

    $nodeStorage = \Drupal::entityManager()->getStorage('node');

    // Query for language ids.
    $query = \Drupal::entityQuery('node')->condition('type', 'language');
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $languageNode = $nodeStorage->load($nid);

      $langKey = $languageNode->get('field_language_key')->value;

      if (count($languageNode->get('field_translations_notification')) == 1) {
        $notification = $nodeStorage->load($languageNode->get('field_translations_notification')[0]->getValue()['target_id']);

        foreach ($notification->get('field_translations') as $translation) {
          if (is_null($mid) || $translation->target_id == $mid) {
            $translations['notification'][$langKey][$translation->key] = $translation->value;
          }
        }
      }

      if (count($languageNode->get('field_translations_ui')) == 1) {
        $ui = $nodeStorage->load($languageNode->get('field_translations_ui')[0]->getValue()['target_id']);

        foreach ($ui->get('field_translations') as $translation) {
          if (is_null($mid) || $translation->target_id == $mid) {
            $translations['ui'][$langKey][$translation->key] = $translation->value;
          }
        }
      }
    }

    return $translations;
  }

  /**
   * Get an array representation of a machine.
   *
   * @param $node
   *   The machine node.
   *
   * @return array
   *   Machine represented as array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *
   */
  public function getMachineArray($node) {

    $brand = $node->get('field_notification_header_brand')->entity;
    if ($brand->get('status')->value) {
      $type = $brand->getMimeType();

      $values= $node->get('field_notification_header_brand')->getValue();
      $brand_alt_text = reset($values)['alt'];

      $path =  \Drupal::service('file_system')->realpath($brand->getFileUri());
      $data = file_get_contents($path);
      $brand = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    else {
      $brand_alt_text = '';
      $brand = NULL;
    }

    $logo = $node->get('field_notification_header_logo')->entity;
    if ($logo->get('status')->value) {
      $type = $logo->getMimeType();

      $values= $node->get('field_notification_header_logo')->getValue();
      $logo_alt_text = reset($values)['alt'];

      $path =  \Drupal::service('file_system')->realpath($logo->getFileUri());
      $data = file_get_contents($path);
      $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    else {
      $logo_alt_text = '';
      $logo = NULL;
    }

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
        'loginAttempts' => [
          'max' => $this->parseInt($node->get('field_fbs_login_attempts_max')->value),
          'timeLimit' => $this->parseInt($node->get('field_fbs_login_attempts_time_li')->value),
        ],
        'onlineState' => [
          'threshold' => $this->parseInt($node->get('field_online_state_threshold')->value),
          'onlineTimeout' => $this->parseInt($node->get('field_online_state_online_to')->value),
          'offlineTimeout' => $this->parseInt($node->get('field_online_state_offline_to')->value),
        ]
      ],
      'ui' => [
        'timeout' => [
          'idleTimeout' => $this->parseInt($node->get('field_timeout')->value),
          'idleWarn' => $this->parseInt($node->get('field_timeout_idle')->value),
        ],
        'binSorting' => [
          'default_bin' => $node->get('field_bin_default')->value ? 'left' : 'right',
          'destinations' => [
            'left' => [
              'id' => 'left',
              'background_color' => $node->get('field_bin_left_background_color')->value,
              'color' => $node->get('field_bin_left_color')->value,
            ],
            'right' => [
              'id' => 'right',
              'background_color' => $node->get('field_bin_right_background_color')->value,
              'color' => $node->get('field_bin_right_color')->value,
            ]
          ],
          // Will be attached later.
          "bins" => []
        ],
        'display_more_materials' => $this->parseBoolean($node->get('field_image_more_materials')->value),
        'display_fines' => $this->parseBoolean($node->get('field_display_fines')->value),
        'login' => [
          'allow_scan' => $this->parseBoolean($node->get('field_login_allow_scan')->value),
          'allow_manual' => $this->parseBoolean($node->get('field_login_allow_manual')->value),
        ],
      ],
      'notification' => [
        'config' => [
          'default_lang' => $node->get('field_notification_default_lang')->value,
          'date_format' => $node->get('field_notification_date_format')->value,
        ],
        'mailer' => [
          'host' => $node->get('field_notification_mailer_host')->value,
          'port' => $node->get('field_notification_mailer_port')->value,
          'secure' => $this->parseBoolean($node->get('field_notification_mailer_secure')->value),
          'from' => $node->get('field_notification_mailer_from')->value,
          'subject' => $node->get('field_notification_mailer_subjec')->value,
        ],
        'header' => [
          'brand' => [
            'base64' => $brand,
            'alt' => $brand_alt_text,
          ],
          'logo' => [
            'base64' => $logo,
            'alt' => $logo_alt_text,
          ],
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

    // Attach notification layout status
    if (isset($node->get('field_notification_layouts_statu')->value)) {
      $allowed = \Drupal\field\Entity\FieldConfig::loadByName('node', 'machine', 'field_notification_layouts_statu')
        ->getSetting('allowed_values');
      foreach ($allowed as $key => $value) {
        $machine['notification']['layouts']['status'][$key] = FALSE;
      }
      foreach ($node->get('field_notification_layouts_statu') as $item) {
        $machine['notification']['layouts']['status'][$item->value] = TRUE;
      }
    }

    // Attach notification layout checkOut
    if (isset($node->get('field_notification_layouts_cout')->value)) {
      $allowed = \Drupal\field\Entity\FieldConfig::loadByName('node', 'machine', 'field_notification_layouts_cout')
        ->getSetting('allowed_values');
      foreach ($allowed as $key => $value) {
        $machine['notification']['layouts']['checkOut'][$key] = FALSE;
      }
      foreach ($node->get('field_notification_layouts_cout') as $item) {
        $machine['notification']['layouts']['checkOut'][$item->value] = TRUE;
      }
    }

    // Attach notification layout checkIn
    if (isset($node->get('field_notification_layouts_cin')->value)) {
      $allowed = \Drupal\field\Entity\FieldConfig::loadByName('node', 'machine', 'field_notification_layouts_cin')
        ->getSetting('allowed_values');
      foreach ($allowed as $key => $value) {
        $machine['notification']['layouts']['checkIn'][$key] = FALSE;
      }
      foreach ($node->get('field_notification_layouts_cin') as $item) {
        $machine['notification']['layouts']['checkIn'][$item->value] = TRUE;
      }
    }

    // Attach notification layout reservations
    if (isset($node->get('field_notification_layouts_reser')->value)) {
      $allowed = \Drupal\field\Entity\FieldConfig::loadByName('node', 'machine', 'field_notification_layouts_reser')
        ->getSetting('allowed_values');
      foreach ($allowed as $key => $value) {
        $machine['notification']['layouts']['reservations'][$key] = FALSE;
      }
      foreach ($node->get('field_notification_layouts_reser') as $item) {
        $machine['notification']['layouts']['reservations'][$item->value] = TRUE;
      }
    }

    // Attach bins to binSorting.
    foreach ($node->get('field_bin_left') as $item) {
      $machine['ui']['binSorting']['bins']["" . $item->value] = 'left';
    }
    foreach ($node->get('field_bin_right') as $item) {
      $machine['ui']['binSorting']['bins']["" . $item->value] = 'right';
    }

    // Attach features.
    if (count($node->get('field_features'))) {
      $machine['ui']['features'] = [];

      foreach ($node->get('field_features') as $ref) {
        $feature = \Drupal::entityManager()
          ->getStorage('node')
          ->load($ref->getValue()['target_id']);


        $machine['ui']['features'][] = [
          'title' => $feature->get('title')->value,
          'icon' => $feature->get('field_icon')->value,
          'require_online' => boolval($feature->get('field_require_online')->value),
          'text' => $feature->get('field_text')->value,
          'url' => $feature->get('field_url')->value,
        ];
      }
    }

    // Attach languages.
    if (count($node->get('field_languages'))) {
      $machine['ui']['languages'] = [];

      foreach ($node->get('field_languages') as $ref) {
        $language = \Drupal::entityManager()
          ->getStorage('node')
          ->load($ref->getValue()['target_id']);

        $machine['ui']['languages'][] = [
          'title' => $language->get('title')->value,
          'text' => $language->get('field_text')->value,
          'langKey' => $language->get('field_language_key')->value,
          'icon' => $language->get('field_icon')->value,
        ];
      }
    }

    return $machine;
  }

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
   * @from http://php.net/manual/en/function.array-merge-recursive.php#92195
   *
   * Modified to ignore null values in array2.
   */
  private function array_merge_recursive_distinct_ignore_nulls(array &$array1, array &$array2) {
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
   * Parse the boolean string value from drupal to true/false.
   *
   * @param $bool
   * @return bool
   */
  private function parseBoolean($bool) {
    return $bool ? TRUE : FALSE;
  }

  /**
   * Parse the int string value from drupal to true/false.
   *
   * @param $int
   * @return int
   */
  private function parseInt($int) {
    return isset($int) ? intval($int) : NULL;
  }

}