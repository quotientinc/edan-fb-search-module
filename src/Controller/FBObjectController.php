<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Defines FBController class.
 */
class FBObjectController extends ControllerBase {

  protected $edanRequestManager;

  public function __construct(EDANRequestManager $edanRequestManager) {
    $this->edanRequestManager = $edanRequestManager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('fb_search.request_manager'));
  }


  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content($id) {
    $results = $this->edanRequestManager->getObjectByID($id);

    return [
      '#theme' => 'display-object',
      '#object' => $results,
    ];
  }
}
