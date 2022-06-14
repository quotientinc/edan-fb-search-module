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
   * Display the markup. Disables page caching for this page.
   *
   * @return array
   *   Return markup array.
   */
  public function content(): array
  {
    \Drupal::service('page_cache_kill_switch')->trigger();

    return [
      '#theme' => 'landing-page'
    ];
  }
}
