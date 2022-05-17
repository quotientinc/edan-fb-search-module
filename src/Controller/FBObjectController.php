<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;


/**
 * Defines FBController class.
 */
class FBObjectController extends ControllerBase {
  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content($id) {
    //$q = str_replace(" ", "+", urldecode($q));
    $request = new EDANRequestManager();
    $results = $request->getObjectByID($id);

    return [
      '#theme' => 'display-object',
      '#object' => $results,
    ];
  }
}
