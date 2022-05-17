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

    $form['edan_server'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EDAN Server'),
      '#default_value' => $config->get('edan.server'),
    ];

    $form['edan_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('edan.app_id'),
    ];

    $form['edan_auth_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Auth Key'),
      '#default_value' => $config->get('edan.auth_key'),
    ];

    $form['edan_tier_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tier Type'),
      '#default_value' => $config->get('edan.tier_type'),
    ];

    $form['edan_rows'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rows per page'),
      '#default_value' => $config->get('edan.rows'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)

      ->set('edan.server', $form_state->getValue('edan_server'))
      ->set('edan.app_id', $form_state->getValue('edan_app_id'))
      ->set('edan.auth_key', $form_state->getValue('edan_auth_key'))
      ->set('edan.tier_type', $form_state->getValue('edan_tier_type'))
      ->set('edan.rows', $form_state->getValue('edan_rows'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
