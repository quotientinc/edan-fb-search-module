<?php
/**
 * @file
 * Contains \Drupal\fb_search\Form\ListSearchForm.
 */
namespace Drupal\fb_search\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
class ListSearchForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'list_search_form';
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#attributes']['class'][] = 'fb-search-list-search-form';

    $form['keyword'] = array(
      '#title' => t('Keyword'),
      '#type' => 'search',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-keyword')
      )
    );

    $form['transcription'] = array(
      '#type' => 'checkbox',
      '#title' => t('Only show records with transcription data.'),
      '#attributes' => array(
        'class' => array('fb-search-checkbox fb-search-transcription')
      )
    );

    $form['fname'] = array(
      '#title' => t('First Name'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-fname')
      )
    );

    $form['lname'] = array(
      '#title' => t('Last Name'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-lname')
      )
    );

    $form['location'] = array(
      '#title' => t('Location'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-lname')
      )
    );

    $form['state'] = array(
      '#title' => t('State'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-state')
      )
    );

    $form['county'] = array(
      '#title' => t('County'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-county')
      )
    );

    $form['city'] = array(
      '#title' => t('City'),
      '#type' => 'textfield',
      '#attributes' => array(
        'class' => array('form-control fb-search-field fb-search-city')
      )
    );

    $rtypes = array(
      "M809" => "Asst. Commissioner – Alabama",
      "M979" => "Asst. Commissioner – Arkansas",
      "M1055" => "Asst. Commissioner – District of Columbia",
      "M798" => "Asst. Commissioner – Georgia",
      "M1027" => "Asst. Commissioner – Louisiana",
      "M1914" => "Asst. Commissioner – Mississippi, pre-Bureau",
      "M826" => "Asst. Commissioner – Mississippi",
      "M843" => "Asst. Commissioner – North Carolina",
      "M869" => "Asst. Commissioner – South Carolina",
      "M999" => "Asst. Commissioner – Tennessee",
      "M821" => "Asst. Commissioner – Texas",
      "M1048" => "Asst. Commissioner – Virginia",
      "M810" => "Education – Alabama",
      "M0980" => "Education – Arkansas",
      "M1056" => "Education – District of Columbia",
      "M799" => "Education – Georgia",
      "M1026" => "Education – Louisiana",
      "M844" => "Education – North Carolina",
      "M1000" => "Education – Tennessee",
      "M822" => "Education – Texas",
      "M1053" => "Education – Virginia",
      "M1900" => "Field Office – Alabama",
      "M1901" => "Field Office – Arkansas",
      "M1902" => "Field Office – District of Columbia",
      "M1869" => "Field Office – Florida",
      "M1903" => "Field Office – Georgia",
      "M1904" => "Field Office – Kentucky",
      "M1905" => "Field Office – Louisiana",
      "M1483" => "Field Office – Louisiana, New Orleans",
      "M1906" => "Field Office – Maryland/Delaware",
      "M1907" => "Field Office – Mississippi",
      "M1908" => "Field Office – Missouri",
      "M1909" => "Field Office – North Carolina",
      "M1910" => "Field Office – South Carolina",
      "M1911" => "Field Office – Tennessee",
      "M1912" => "Field Office – Texas",
      "M1913" => "Field Office – Virginia",
      "M742" => "Headquarters – Records",
      "M752" => "Headquarters – Registers & Letters",
      "M803" => "Headquarters – Education",
      "M1875" => "Headquarters – Marriage ",
      "M2029" => "Miscellaneous – Adjutant General",
      "M816" => "Miscellaneous – Freedmen’s Savings and Trust",
    );

    $form['rtype'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Publication'),
      '#options' => $rtypes,
      "#empty_option" => $this->t('All Publications'),
      "#empty_value" => "",
      '#attributes' => array(
        'class' => array('fb-search-field fb-search-rtype')
      )
    ];

    $form['single_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Single Date'),
    ];

    $form['date'] = [
      '#type' => 'fieldset',
      '#title' => 'Date Range',
      '#attributes' => array(
        'class' => array("fb-search-fieldset fb-search-dates")
      )
    ];

    $form['date']['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start Date'),
    ];

    $form['date']['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End Date'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
     '#type' => 'submit',
     '#value' => $this->t('Search'),
     '#button_type' => 'primary'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $q = "*";
    $query = [];

    $fqs = [];

    if(!empty($values['keyword']))
    {
      $q = $values['keyword'];
      $query['edan_q'] = $q;
    }

    if(!empty($values['transcription']) && $values['transcription'])
    {
      $fqs[] = "p.nmaahc_fb.index.content.transasset.projectid:*";
    }

    if(!empty($values['fname']))
    {
      $fqs[] = "p.nmaahc_fb.pr_name_gn:" . $values['fname'];
    }

    if(!empty($values['lname']))
    {
      $fqs[] = "p.nmaahc_fb.pr_name_surn:" . $values['lname'];
    }

    if(!empty($values['location']))
    {
      $fqs[] = "p.nmaahc_fb.location:" . $values['location'];
    }

    if(!empty($values['state']))
    {
      $fqs[] = "p.nmaahc_fb.index.event_state:" . $values['state'];
    }

    if(!empty($values['county']))
    {
      $fqs[] = "p.nmaahc_fb.index.event_county:" . $values['county'];
    }

    if(!empty($values['city']))
    {
      $fqs[] = "p.nmaahc_fb.index.event_city:" . $values['city'];
    }

    if(!empty($values['rtype']))
    {
      $fqs[] = "p.nmaahc_fb.record_pub_number:" . $values['rtype'];
    }

    $date_set = FALSE;

    if(!empty($values['single_date']))
    {
      $date_set = TRUE;
      $fqs[] = "p.nmaahc_fb.index.search_date:" . $values['single_date'];
    }

    if(!$date_set && !empty($values['start_date']) && !empty($values['end_date']))
    {
      $fqs[] = "p.nmaahc_fb.index.search_date:[" . $values['start_date'] . " TO " . $values['end_date'] . "]";
    }

    $query['edan_fq'] = $fqs;

    $form_state->setRedirect('fb_search.search', [], ['query' => $query]);

    return;
  }
}
