<?php
/**
 * @file
 * Contains \Drupal\fb_search\Form\LandingPageForm.
 */
namespace Drupal\fb_search\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
class LandingPageForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'landing_page_form';
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#attributes']['class'][] = 'fb-search-landing-page-form';

    $form['search'] = array(
      '#type' => 'search',
      '#attributes' => array(
        'placeholder' => t("Search Freedmen's Bureau Records"),
        'class' => array('form-control fb-search-keyword')
      )
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
     '#type' => 'submit',
     '#value' => $this->t('Save'),
     '#button_type' => 'primary',
     '#attributes' => array(
       'style' => array('display: none;')
     ),
    );

    $form['#theme'] = 'landing-page';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $q = "*";

    if(!empty($values["search"]))
    {
      $q = $values["search"];
    }

    $form_state->setRedirect('fb_search.search', ['q' => $q, 'place' => 0]);

    /*$form_state->setRedirect('fb_search.search', [
      'q' => $q,
      'place' => 0
    ],
    ['query' => array("fname" => "Robert")]);*/

    return;
  }
}
