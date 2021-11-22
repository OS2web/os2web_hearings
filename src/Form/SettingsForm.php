<?php

namespace Drupal\os2web_hearings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Name of the config.
   *
   * @var string
   */
  public static $configName = 'os2web_hearings.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2web_hearings_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [SettingsForm::$configName];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['sbsys_email'] = [
      '#type' => 'email',
      '#title' => $this->t('SBSYS Email'),
      '#description' => $this->t('Email adresse for at sende høring eller afgørels, når det frist dato er kommet.'),
      '#weight' => '0',
      '#default_value' => $this->config(SettingsForm::$configName)
        ->get('sbsys_email'),
    ];

    $form['disable_comments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Diabled comments (globally)'),
      '#description' => $this->t('This decides if all hearing are present as closed - commenting disabled.'),
      '#default_value' => $this->config(SettingsForm::$configName)
        ->get('disable_comments'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $config = $this->config(SettingsForm::$configName);
    foreach ($values as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();

    // Clearing cache to render the Hearings correctly (disable_comments
    // changes Hearing rendering).
    \Drupal::service('cache.render')->invalidateAll();

    parent::submitForm($form, $form_state);
  }

}
