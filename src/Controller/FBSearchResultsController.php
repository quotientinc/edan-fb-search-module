<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\edan\EdanClient\EdanClient;


/**
 * Defines FBController class.
 */
class FBSearchResultsController extends ControllerBase {

  protected $edanRequestManager;

  public function __construct(EDANRequestManager $edanRequestManager) {
    $this->edanRequestManager = $edanRequestManager;
  }

  public static function create(ContainerInterface $container) {
    $edanRequestManager = $container->get('fb_search.request_manager');
    return new static($container->get('fb_search.request_manager'));
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content($q, $place) {
    $q = str_replace(" ", "+", urldecode($q));
    $results = $this->edanRequestManager->getNmaahcFBList($q, $place);

    $fb_config = \Drupal::config('fb_search.settings');
    $rows = $fb_config->get('display.rows');

    return [
      '#theme' => 'search-results',
      '#results' => $results,
      '#q' => str_replace("+", " ", $q),
      '#place' => $place,
      '#rows' => $rows,
    ];
  }
}
