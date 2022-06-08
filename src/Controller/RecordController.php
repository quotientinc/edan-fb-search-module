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

  public function getFqs()
  {
    $fqs = [];

    if(\Drupal::request()->query->has('destination'))
    {
      $query_str = parse_url(\Drupal::request()->query->get('destination'), PHP_URL_QUERY);
      $query = [];
      parse_str($query_str, $query);

      $query = $query['edan_fq'];

      foreach($query as $fq)
      {
        $fq = explode(':', $fq, 2);
        $fqs[$fq[0]] = $fq[1];
      }
    }

    return $fqs;
  }

  private function matchField(&$matched, $row, $value, $column_name, $field_name = '')
  {
    $column = $row[$column_name];

    foreach($column as $c)
    {
      if(($c['label'] == $field_name || empty($field_name)) && trim($value) == trim($c['content']))
      {
        $matched[] = $row;
        return TRUE;
      }
    }

    return FALSE;
  }

  private function sortRows($object)
  {
    if(!isset($object['content']['indexed_rows']))
    {
        return [];
    }

    $matched = [];
    $unmatched = [];

    $rows = $object['content']['indexed_rows'];

    $query = [];

    $fqs = $this->getFqs();

    foreach($rows as $row)
    {
      if(isset($fqs['p.nmaahc_fb.pr_name_gn']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.pr_name_gn'], 'name', 'First Name'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.pr_name_surn']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.pr_name_surn'], 'name', 'Last Name'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.location']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_country'], 'location'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.index.event_country']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_country'], 'location', 'Country'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.index.event_state']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_state'], 'location', 'State'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.index.event_county']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_county'], 'location', 'County'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.index.event_district']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_district'], 'location', 'District'))
      {
        continue;
      }

      if(isset($fqs['p.nmaahc_fb.index.event_city']) && $this->matchField($matched, $row, $fqs['p.nmaahc_fb.index.event_city'], 'location', 'City'))
      {
        continue;
      }

      $unmatched[] = $row;
    }

    return array('total' => count($rows), 'matched' => $matched, 'unmatched' => $unmatched, 'fqs' => $fqs);
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
      //$destination = str_replace("+", " ", $destination);
    }

    return [
      '#theme' => 'display-object',
      '#object' => $results,
      '#destination' => $destination,
      '#query' => json_encode($this->sortRows($results), JSON_PRETTY_PRINT),
      '#title' => $title,
    ];
  }
}
