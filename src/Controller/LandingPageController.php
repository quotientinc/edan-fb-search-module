<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Defines FBController class.
 */
class LandingPageController extends ControllerBase {

  public function __construct(){}

  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content()
  {
    \Drupal::service('page_cache_kill_switch')->trigger();

    return [
      '#theme' => 'landing-page'
    ];
  }
}
