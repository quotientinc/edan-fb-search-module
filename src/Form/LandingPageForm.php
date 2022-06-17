<?php

namespace Drupal\fb_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class LandingPageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'landing_page_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'fb-search-landing-page-form';

    $form['search'] = [
      '#type' => 'search',
      '#attributes' => [
        'placeholder' => $this->t("Search by keyword"),
        'class' => ['form-control fb-search-field fb-search-keyword'],
      ],
    ];

    $form['location'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t("Search by location"),
        'class' => ['form-control fb-search-field fb-search-location'],
      ],
    ];

    $form['fname'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t("Search by first name"),
        'class' => ['form-control fb-search-field fb-search-fname'],
      ],
    ];

    $form['lname'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t("Search by last name"),
        'class' => ['form-control fb-search-field fb-search-lname'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#attributes' => [
        'style' => ['display: none;'],
      ],
    ];

    $form['#theme'] = 'landing-page';

    $form['#attached'] = [
      'library' => [
        'fb_search/fb-search-attachments',
        'fb_search/bootstrap4',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $query = ['place' => 0];

    $q = "*:*";
    $fqs = [];

    if (!empty($values["search"])) {
      $q = $values["search"];
      $query['edan_q'] = $q;
    }

    if (!empty($values['fname'])) {
      $fqs[] = "p.nmaahc_fb.pr_name_gn:" . $values['fname'];
    }

    if (!empty($values['lname'])) {
      $fqs[] = "p.nmaahc_fb.pr_name_surn:" . $values['lname'];
    }

    if (!empty($values['location'])) {
      $fqs[] = "p.nmaahc_fb.location:" . $values['location'];
    }

    if (!empty($fqs)) {
      $query['edan_fq'] = $fqs;
    }

    $form_state->setRedirect('fb_search.search', $query);

    return;
  }

}
