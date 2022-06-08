<?php

namespace Drupal\fb_search\EDAN;

use Drupal\fb_search\EDAN\EDANInterface;
use Drupal\edan\Connector\EdanConnectorService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EDANRequestManager
{
  protected $connector;

  public function __construct(EdanConnectorService $edanConnector)
  {
    $this->connector = $edanConnector;
  }

  public static function create(ContainerInterface $container) {
		 return new static($container->get('edan.connector'));
	}

  private function parseFqsArray($fqs)
  {
    $parsed_fqs = [];

    foreach($fqs as $fq)
    {
      $fq = explode(":", $fq, 2);
      $parsed_fqs[$fq[0]] = $fq[1];
    }

    return $parsed_fqs;
  }

  private function getColumnFieldMapping($fqs)
  {
    $fqs = $this->parseFqsArray($fqs);
    $mapping = [
      'name' => [],
      'location' => [],
      'date' => [],
      'misc' => []
    ];

    foreach($fqs as $key => $value)
    {
      switch($key)
      {
        case 'p.nmaahc_fb.pr_name_gn':
          $mapping['name'][] = ['field' => 'First Name', 'value' => $value];
          break;
        case 'p.nmaahc_fb.pr_name_surn':
          $mapping['name'][] = ['field' => 'Last Name', 'value' => $value];
          break;
        case 'p.nmaahc_fb.location':
          $mapping['location'][] = ['field' => '', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.event_country':
          $mapping['location'][] = ['field' => 'Country', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.event_state':
          $mapping['location'][] = ['field' => 'State', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.event_county':
          $mapping['location'][] = ['field' => 'County', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.event_district':
          $mapping['location'][] = ['field' => 'District', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.event_city':
          $mapping['location'][] = ['field' => 'City', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.pr_occupation':
          $mapping['misc'][] = ['field' => 'Occupation', 'value' => $value];
          break;
        case 'p.nmaahc_fb.index.search_date':
          $mapping['date'][] = ['field' => 'Display Date', 'value' => $value];
          break;
      }
    }

    return $mapping;
  }

  private function getColumnDateRange($date)
  {
    if(strpos($date, "-") !== false)
    {
      $dates = explode("-", $date);
      $start = $dates[0] . "-01-01";
      $end = $dates[1] . "-12-31";

      return ['start' => strtotime($start), 'end' => strtotime($end)];
    }

    $date_arr = date_parse($date);

    if($date_arr)
    {
        $date_str = $date_arr['year'] . "-" . $date_arr['month'] . "-" . $date_arr['day'];

        return ['start' => strtotime($date_str), 'end' => strtotime($date_str)];
    }

    return FALSE;
  }

  private function getColumnDate($date)
  {
    $date_arr = date_parse($date);

    if($date_arr)
    {
      return strtotime($date_arr['year'] . "-" . $date_arr['month'] . "-" . $date_arr['day']);
    }

    return FALSE;
  }

  private function checkDateRangeOverlap($fq_range, $column_range)
  {
    //
    $start_check = $fq_range['end'] >= $column_range['start'];
    $end_check = $fq_range['start'] <= $column_range['end'];

    return $start_check && $end_check;
  }

  private function checkInDateRange($range, $date)
  {
    return $date <= $range['end'] && $date >= $range['start'];
  }

  private function compareDates($fq_date, $column_date)
  {
    return $fq_date == $column_date;
  }

  //fbs-1647632055567-1647632225948-0
  private function parseFqDate($date)
  {
    $date = str_replace(']', '', str_replace('[', '', $date));
    $date = explode(" TO ", $date);

    return ['start' => strtotime($date[0]), 'end' => strtotime($date[1])];
  }

  private function matchDateField($row, $value)
  {
    if(!isset($row['date']))
    {
      return FALSE;
    }

    $fq_date = NULL;
    $fq_range = NULL;

    if(strpos($value, ' TO ') !== FALSE)
    {
      $fq_range = $this->parseFqDate($value);
    }
    else
    {
      $fq_date = strtotime($value);
    }

    $column = $row['date'];

    foreach($column as $c)
    {
      if($c['label'] == 'Display Date')
      {
        $column_date_string = $c['content'];

        $column_range = NULL;
        $column_date = NULL;

        if(strpos($column_date_string, "-") !== false)
        {
          $column_range = $this->getColumnDateRange($c['content']);
        }
        else
        {
          $column_date = $this->getColumnDate($c['content']);
        }

        if($fq_range)
        {
          if($column_range)
          {
            return $this->checkDateRangeOverlap($fq_range, $column_range);
          }
          else
          {
            return $this->checkInDateRange($fq_range, $column_date);
          }
        }
        else
        {
          if($column_range)
          {
            return $this->checkInDateRange($column_range, $fq_date);
          }
          else
          {
            return $this->compareDates($fq_date, $column_date);
          }
        }
      }
    }

    return FALSE;
  }

  private function matchField($row, $column_name, $value, $field_name = '')
  {
    if(!isset($row[$column_name]))
    {
        return FALSE;
    }

    $column = $row[$column_name];

    foreach($column as $c)
    {
      if(($c['label'] == $field_name || empty($field_name)) && trim($value) == trim($c['content']))
      {
        return TRUE;
      }
    }

    return FALSE;
  }

  private function getSearchMatchCounts(&$results, $fqs)
  {
    $rows = $results['data']['rows'];

    for($i = 0; $i < count($rows); $i++)
    {
      $row = $rows[$i];

      if(!isset($row['content']['indexed_rows']))
      {
        continue;
      }

      $indexed_rows = $row['content']['indexed_rows'];
      $mapping = $this->getColumnFieldMapping($fqs);


      $results['data']['rows'][$i]['content']['matching_row_count'] = 0;
      $results['data']['rows'][$i]['content']['date_match'] = [];

      foreach($indexed_rows as $idx)
      {
        $row_matches = FALSE;

        foreach($mapping as $column => $fields)
        {
          if($row_matches)
          {
            break;
          }

          foreach($fields as $field)
          {
            $value = $field['value'];
            $name = $field['field'];

            if($column == 'date')
            {
              $results['data']['rows'][$i]['content']['date_match'][] = ['row' => $idx, 'fq' => $value, 'success' => $this->matchDateField($idx, $value)];
              if($this->matchDateField($idx, $value))
              {
                $row_matches = TRUE;
                break;
              }
            }
            else
            {
              $results['data']['rows'][$i]['content']['date_match'][] = NULL;
              if($this->matchField($idx, $column, $value, $name))
              {
                $row_matches = TRUE;
                break;
              }
            }
          }
        }

        if($row_matches)
        {
          $results['data']['rows'][$i]['content']['matching_row_count'] += 1;
        }
      }
    }
  }

  private function getMatchedRows(&$results, $fqs)
  {
    $indexed_rows = $results['data']['content']['indexed_rows'];

    $results['data']['content']['matched_rows'] = [];
    $results['data']['content']['unmatched_rows'] = [];

    $mapping = $this->getColumnFieldMapping($fqs);

    foreach($indexed_rows as $row)
    {
      $row_matches = FALSE;

      foreach($mapping as $column => $fields)
      {
        foreach($fields as $field)
        {
          $name = $field['field'];
          $value = $field['value'];

          if($column == 'date')
          {
            if($this->matchDateField($row, $value))
            {
              $row_matches = TRUE;

              for($i = 0; $i < count($row[$column]); $i++)
              {
                $entry = $row[$column][$i];

                if(empty($name) || $entry['label'] == $name)
                {
                  $row[$column][$i]['matched'] = TRUE;
                }
              }
            }
          }
          else
          {
            if($this->matchField($row, $column, $value, $name))
            {
              $row_matches = TRUE;

              for($i = 0; $i < count($row[$column]); $i++)
              {
                $entry = $row[$column][$i];
                if(empty($name) || $entry['label'] == $name)
                {
                  $row[$column][$i]['matched'] = TRUE;
                }
              }
            }
          }
        }
      }

      if($row_matches)
      {
        $results['data']['content']['matched_rows'][] = $row;
      }
      else
      {
        $results['data']['content']['unmatched_rows'][] = $row;
      }
    }
  }

  public function getNmaahcFBList($q, $fqs=[], $start=0, $rows=10)
  {
    $startrows = $start * $rows;

    $fqs[] = "type:nmaahc_fb";

    //\Drupal::logger('fb-search')->notice("$fqs");

    $fields = [
      'p.nmaahc_fb.pr_name_gn',
      'p.nmaahc_fb.pr_name_surn',
      'p.nmaahc_fb.location',
      'p.nmaahc_fb.index.event_country',
      'p.nmaahc_fb.index.event_state',
      'p.nmaahc_fb.index.event_county',
      'p.nmaahc_fb.index.event_district',
      'p.nmaahc_fb.index.event_city',
      'p.nmaahc_fb.index.pr_occupation',
      'p.nmaahc_fb.record_pub_number'
    ];

    $params = [
      "rows" => $rows,
      "start" => $startrows,
      "q" => $q,
      "fqs" => $fqs,
      "facet" => "true",
      "facet.field" => $fields,
      "facet.sort" => "count",
      "facet.limit" => "-1"
    ];

    $results = $this->connector->runParamQuery($params);
    if(isset($results['data']['rows']))
    {
      $this->getSearchMatchCounts($results, $fqs);
    }

    return $results["data"];
  }

  public function getObjectByID($id, $fqs=[])
  {
    $results = $this->connector->getRecord($id, "id", "nmaahc_fb");

    if(!empty($fqs) && isset($results['data']['content']))
    {
      $this->getMatchedRows($results, $fqs);
    }

    return $results["data"];
  }
}
