<?php
namespace Drupal\fb_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class fb_searchSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'fb_search.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fb_search_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['search'] = array(
      '#type' => 'fieldset',
      '#title' => t('Search Settings'),
      '#collapsible' => TRUE, // Added
      '#collapsed' => FALSE,  // Added
    );

    $form['search']['first_name'] = [
      '#type' => 'number',
      '#title' => $this->t('Search boost value for first name'),
      '#default_value' => $config->get('search.fname'),
      '#step' => '.1',
    ];

    $form['search']['last_name'] = [
      '#type' => 'number',
      '#title' => $this->t('Search boost value for last name'),
      '#default_value' => $config->get('search.lname'),
      '#step' => '.1',
    ];

    $form['search']['location'] = [
      '#type' => 'number',
      '#title' => $this->t('Search boost value for location'),
      '#default_value' => $config->get('search.location'),
      '#step' => '.1',
    ];

    $form['search']['rtype'] = [
      '#type' => 'number',
      '#title' => $this->t('Search boost value for record type'),
      '#default_value' => $config->get('search.rtype'),
      '#step' => '.1',
    ];

    $form['display'] = array(
      '#type' => 'fieldset',
      '#title' => t('Display Settings'),
      '#collapsible' => TRUE, // Added
      '#collapsed' => FALSE,  // Added
    );

    $form['display']['rows'] = [
      '#type' => 'number',
      '#title' => $this->t('Rows per page'),
      '#default_value' => $config->get('display.rows'),
      '#step' => '1',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)

      ->set('search.fname', $form_state->getValue('first_name'))
      ->set('search.lname', $form_state->getValue('last_name'))
      ->set('search.location', $form_state->getValue('location'))
      ->set('search.rtype', $form_state->getValue('rtype'))
      ->set('display.rows', $form_state->getValue('rows'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
