<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\edan\EdanClient\EdanClient;
use \Drupal\Core\Url;


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

  private function getURLPrefix($edan_q, $edan_fq)
  {
    $path = Url::fromRoute('<current>')->toString();

    $query = [];

    if(!empty(trim($edan_q)) && $edan_q != "*")
    {
      $query['edan_q'] = $edan_q;
    }

    if(!empty($edan_fq))
    {
      $query['edan_fq'] = $edan_fq;
    }

    $query_str = http_build_query($query);

    if(!empty(trim($query_str)))
    {
      return "$path?$query_str&page=";
    }

    return "$path?page=";
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\fb_search\Form\ListSearchForm');
    $query = [];

    $params = [];

    $edan_q = "*";

    if(\Drupal::request()->query->has('edan_q'))
    {
      $edan_q = \Drupal::request()->query->get('edan_q');
      $edan_q = str_replace(" ", "+", urldecode($edan_q));
      $query['edan_q'] = $edan_q;
      $form['keyword']['#value'] = $edan_q;
    }

    $fqs = [];

    $test_var = "";

    if(\Drupal::request()->query->has('edan_fq'))
    {
      $fqs = \Drupal::request()->query->get('edan_fq');
      $query['edan_fq'] = $fqs;

      foreach($fqs as $fq)
      {
        $fq_set = explode(":", $fq, 2);
        $name = $fq_set[0];
        $value = explode("^", $fq_set[1])[0];

        switch($name)
        {
          case "p.nmaahc_fb.pr_name_gn":
            $form['fname']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.content.transasset.projectid":
            $form['transcription']['#checked'] = TRUE;
            break;
          case "p.nmaahc_fb.pr_name_surn":
            $form['lname']['#value'] = $value;
            break;
          case "p.nmaahc_fb.location":
            $form['location']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.event_country":
            $form['country']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.event_state":
            $form['state']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.event_county":
            $form['county']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.event_district":
            $form['district']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.event_city":
            $form['city']['#value'] = $value;
            break;
          case "p.nmaahc_fb.record_pub_number":
            $form['rtype']['#value'] = $value;
            break;
          case "p.nmaahc_fb.index.search_date":
            if(strpos($value, "TO") !== false)
            {
              $value = str_replace("[", "", $value);
              $value = str_replace("]", "", $value);

              $dates = explode(" TO ", $value);
              $test_var = json_encode($dates);

              $form['date']['start_date']['#value'] = $dates[0];
              $form['date']['end_date']['#value'] = $dates[1];
            }
            else
            {
              $form['single_date']['#value'] = $value;
            }

            break;
        }
      }
    }

    $place = 0;

    if(\Drupal::request()->query->has('page'))
    {
      $place = \Drupal::request()->query->get('page');
    }

    $fb_config = \Drupal::config('fb_search.settings');
    $rows = $fb_config->get('display.rows');

    $results = $this->edanRequestManager->getNmaahcFBList($edan_q, $place, $params, $rows, $fqs);

    $response = [];

    $response['navigation'] = array(
      'rows_per_page' => $rows,
      'record_count' => $results['rowCount'],
      'page_count' => ceil($results["rowCount"] / $rows),
      'current_page' => $place,
      'url_prefix' => $this->getURLPrefix($edan_q, $fqs),
    );

    $response['query'] = array(
      'edan_q' => str_replace("+", " ", $edan_q),
      'edan_fq' => $fqs,
    );

    $response['results'] = $results['rows'];

    \Drupal::service('page_cache_kill_switch')->trigger();

    return [
      '#theme' => 'search-results',
      '#response' => $response,
      '#form' => $form,
    ];
  }
}
