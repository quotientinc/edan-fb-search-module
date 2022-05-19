<?php

namespace Drupal\fb_search\EDAN;

use Drupal\fb_search\EDAN\EDANInterface;
use Drupal\edan\Connector\EdanConnectorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EDANRequestManager
{
  private $rows;
  protected $connector;

  public function __construct(EdanConnectorService $edanConnector)
  {
    $this->connector = $edanConnector;

    $fb_config = \Drupal::config('fb_search.settings');
    $this->rows = $fb_config->get('display.rows');
  }

  public static function create(ContainerInterface $container) {
		 return new static($container->get('edan.connector'));
	}

  public function getNmaahcFBList($q, $start=0)
  {
    $startrows = $start * $this->rows;

    $params = [
      "rows" => $this->rows,
      "start" => $startrows,
      "facets" => false,
      "q" => $q,
      "fqs" => '["type:\"nmaahc_fb\""]'
    ];

    $results = $this->connector->runParamQuery($params);
    return $results["data"];
  }

  public function getObjectByID($id)
  {
    $results = $this->connector->getRecord($id, "id", "nmaahc_fb");
    return $results["data"];
  }
}
