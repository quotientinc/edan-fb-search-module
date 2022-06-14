<?php
namespace Drupal\fb_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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

    $form['display'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Display Settings'),
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
      ->set('display.rows', $form_state->getValue('rows'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
