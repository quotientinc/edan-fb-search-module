<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Defines FBController class.
 */
class RecordController extends ControllerBase {

  protected $edanRequestManager;

  public function __construct(EDANRequestManager $edanRequestManager) {
    $this->edanRequestManager = $edanRequestManager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('fb_search.request_manager'));
  }

  public function getQuery()
  {
    if(\Drupal::request()->query->has('destination'))
    {
      $query_str = parse_url(\Drupal::request()->query->get('destination'), PHP_URL_QUERY);
      $query = [];
      parse_str($query_str, $query);

      if(isset($query['edan_fq']))
      {
        return $query['edan_fq'];
      }
    }

    return [];
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content($id) {
    $results = $this->edanRequestManager->getObjectByID($id, $this->getQuery());

    $title = isset($results['content']['image_title']) ? $results['content']['image_title'] : "Object Not Found";

    $destination = NULL;

    if(\Drupal::request()->query->has('destination'))
    {
      $destination = urldecode(\Drupal::request()->query->get('destination'));
    }

    return [
      '#theme' => 'display-object',
      '#object' => $results,
      '#destination' => $destination,
      '#title' => $title,
    ];
  }
}
