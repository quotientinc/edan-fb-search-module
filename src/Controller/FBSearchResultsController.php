<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;


/**
 * Defines FBController class.
 */
class FBSearchResultsController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content($q, $place) {
    $q = str_replace(" ", "+", urldecode($q));
    $request = new EDANRequestManager();
    $results = $request->getNmaahcFBList($q, $place);

    $fb_config = \Drupal::config('fb_search.settings');
    $rows = $fb_config->get('edan.rows');

    return [
      '#theme' => 'search-results',
      '#results' => $results,
      '#q' => str_replace("+", " ", $q),
      '#place' => $place,
      '#rows' => $rows,
    ];
  }
}
