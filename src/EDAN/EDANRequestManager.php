<?php

namespace Drupal\fb_search\EDAN;

use Drupal\fb_search\EDAN\EDANInterface;
use Drupal\edan\Connector\EdanConnectorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EDANRequestManager
{
  protected $connector;
  protected $fname_boost;
  protected $lname_boost;
  protected $location_boost;
  protected $rtype_boost;

  public function __construct(EdanConnectorService $edanConnector)
  {
    $this->connector = $edanConnector;

    $fb_config = \Drupal::config('fb_search.settings');
    $this->fname_boost = $fb_config->get('search.fname');
    $this->lname_boost = $fb_config->get('search.lname');
    $this->location_boost = $fb_config->get('search.location');
    $this->rtype_boost = $fb_config->get('search.rtype');
  }

  public static function create(ContainerInterface $container) {
		 return new static($container->get('edan.connector'));
	}

  protected function processParams($params)
  {
    $fqs = [];

    array_push($fqs, "type:nmaahc_fb");

    foreach($params as $key => $value)
    {
      $value = str_replace("*", "", $value);
      /*<int name="p.nmaahc_fb.index.event_city">290239</int>
        <int name="p.nmaahc_fb.index.event_country">588903</int>
        <int name="p.nmaahc_fb.index.event_county">185066</int>
        <int name="p.nmaahc_fb.index.event_district">15188</int>
        <int name="p.nmaahc_fb.index.event_state">556494</int>
      */

      switch($key)
      {
        case "fname":
          array_push($fqs, "p.nmaahc_fb.pr_name_gn:$value^$this->fname_boost");
          break;
        case "lname":
          array_push($fqs, "p.nmaahc_fb.pr_name_surn:$value^$this->lname_boost");
          break;
        case "location":
          array_push($fqs, "p.nmaahc_fb.location:$value^$this->location_boost");
          break;
        case "rtype":
          array_push($fqs, "p.nmaahc_fb.record_pub_number:$value^$this->rtype_boost");
          break;
        case "date":
          array_push($fqs, "p.nmaahc_fb.index.search_date:[" . $value[0] . " TO " . $value[1] . "]");
          break;
      }
    }

    return json_encode($fqs);
  }

  public function getNmaahcFBList($q, $start=0, $fqs=[], $rows=10, $facets=[])
  {
    $fqs = $this->processParams($fqs);
    $startrows = $start * $rows;

    $facets[] = "type:nmaahc_fb";

    //\Drupal::logger('fb-search')->notice("$fqs");

    $params = [
      "rows" => $rows,
      "start" => $startrows,
      "facets" => false,
      "q" => $q,
      "fqs" => $facets
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
