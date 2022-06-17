<?php

namespace Drupal\fb_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fb_search\EDAN\EDANRequestManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Defines FBController class.
 */
class ListSearchController extends ControllerBase {

  protected $edanRequestManager;

  /**
   *
   */
  public function __construct(EDANRequestManager $edanRequestManager) {
    $this->edanRequestManager = $edanRequestManager;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('fb_search.request_manager'));
  }

  /**
   *
   */
  private function getURLPrefix(): string {
    $query = $this->getQuery();

    $path = Url::fromRoute('<current>')->toString();

    if (empty(trim($query['edan_q'])) || $query['edan_q'] === "*:*") {
      unset($query['edan_q']);
    }

    if (empty(trim($query['edan_fq']))) {
      unset($query['edan_fq']);
    }

    $query_str = http_build_query($query);

    if (!empty(trim($query_str))) {
      return "$path?$query_str";
    }

    return "$path?";
  }

  /**
   *
   */
  private function getQuery(): array {
    $query = [
      'edan_q' => '*:*',
      'edan_fq' => [],
    ];

    if (\Drupal::request()->query->has('edan_q')) {
      $query['edan_q'] = \Drupal::request()->query->get('edan_q');
    }

    if (\Drupal::request()->query->has('edan_fq')) {
      $query['edan_fq'] = \Drupal::request()->query->get('edan_fq');
    }

    return $query;
  }

  /**
   *
   */
  private function getResponse(): array {
    $response = [];

    $response['query'] = $this->getQuery();

    $edan_q = $response['query']['edan_q'];
    $edan_fq = $response['query']['edan_fq'];

    $page = 0;

    if (\Drupal::request()->query->has('page')) {
      $page = \Drupal::request()->query->get('page');
    }

    $fb_config = \Drupal::config('fb_search.settings');
    $rows = $fb_config->get('display.rows');

    $results = $this->edanRequestManager->getNmaahcFBList($edan_q, $edan_fq, $page, $rows);

    $response['navigation'] = $this->getNavigation($results, $rows, $page);

    $response['results'] = $results['rows'] ?? [];

    if (isset($results['facets'])) {
      $response['facets'] = $results['facets'];
    }

    return $response;
  }

  /**
   *
   */
  private function updateForm(&$form): void {
    if (\Drupal::request()->query->has('edan_q') && \Drupal::request()->query->get('edan_q') !== '*:*') {
      $form['keyword']['#value'] = \Drupal::request()->query->get('edan_q');
    }

    if (\Drupal::request()->query->has('edan_fq')) {
      $fqs = \Drupal::request()->query->get('edan_fq');

      foreach ($fqs as $fq) {
        $fq_set = explode(":", $fq, 2);
        $name = $fq_set[0];
        $value = explode("^", $fq_set[1])[0];

        switch ($name) {
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

          case "p.nmaahc_fb.index.pr_occupation":
            $form['occupation']['#value'] = $value;
            break;

          case "p.nmaahc_fb.record_pub_number":
            $form['rtype']['#value'] = $value;
            break;

          case "p.nmaahc_fb.index.search_date":
            if (strpos($value, "TO") !== FALSE) {
              $value = str_replace(["[", "]"], "", $value);

              $dates = explode(" TO ", $value);

              $form['date']['start_date']['#value'] = $dates[0];
              $form['date']['end_date']['#value'] = $dates[1];
            }
            else {
              $form['single_date']['#value'] = $value;
            }

            break;
        }
      }
    }
  }

  /**
   *
   */
  private function getNavigation($results, $rows, $page) {
    $navigation = [
      'rows_per_page' => $rows,
      'record_count' => 0,
      'page_count' => 0,
      'current_page' => $page,
      'url_prefix' => $this->getURLPrefix(),
    ];

    if (isset($results['rowCount'])) {
      $navigation['record_count'] = $results['rowCount'];
      $navigation['page_count'] = ceil($results["rowCount"] / $rows);
    }

    return $navigation;
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\fb_search\Form\ListSearchForm');

    $this->updateForm($form);

    $response = $this->getResponse();

    // $response['facets'] = json_encode($this->edanRequestManager->getFacets($response['query']['edan_q'], $response['query']['edan_fq']), JSON_PRETTY_PRINT);
    \Drupal::service('page_cache_kill_switch')->trigger();

    return [
      '#theme' => 'search-results',
      '#response' => $response,
      '#form' => $form,
    ];
  }

}
