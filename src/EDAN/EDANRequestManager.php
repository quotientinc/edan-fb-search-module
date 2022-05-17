<?php

namespace Drupal\fb_search\EDAN;

use Drupal\fb_search\EDAN\EDANInterface;
//use Drupal\edan\EdanManagerBase;

class EDANRequestManager
{
  private $edan;
  private $rows;
  private $service;

  public function __construct()
  {
    $fb_config = \Drupal::config('fb_search.settings');

    $server = $fb_config->get('edan.server');
    $app_id = $fb_config->get('edan.app_id');
    $auth_key = $fb_config->get('edan.auth_key');
    $tier_type = $fb_config->get('edan.tier_type');

    $this->edan = new EDANInterface($server, $app_id, $auth_key, $tier_type);

    //$this->service = $fb_config->get('edan.service');
    $this->rows = $fb_config->get('edan.rows');
  }

  public function getNmaahcFBList($q, $start=0)
  {
    $service = 'metadata/v2.0/metadata/search.htm';

    $info = '';

    $fqs = 'fqs=["type:\"nmaahc_fb\""]';
    $startrows = $start * $this->rows;
    $uri_string = "rows=$this->rows&start=$startrows&facets=false&q=$q&$fqs";

    $results = $this->edan->sendRequest($uri_string, $service, FALSE, $info);

    if (is_array($info))
    {
      if ($info['http_code'] == 200)
      {
        return json_decode($results);
      }
      else
      {
        return false;
      }
    }
    else
    {
      return false;
    }
  }

  public function getObjectByID($id)
  {
      $service = 'content/v2.0/content/getContent.htm';

      $info = '';
      $uri_string = "id=$id";

      $results = $this->edan->sendRequest($uri_string, $service, FALSE, $info);

      if (is_array($info))
      {
        if ($info['http_code'] == 200)
        {
          return json_decode($results);
        }
        else
        {
          return false;
        }
      }
      else
      {
        return false;
      }
  }
}
