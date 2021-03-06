<?php

namespace Drupal\adstxt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure adstxt settings for this site.
 */
class AdsTxtAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adstxt_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['adstxt.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adstxt.settings');

    $form['adstxt_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Contents of ads.txt'),
      '#default_value' => $config->get('content'),
      '#cols' => 60,
      '#rows' => 20,
    ];

    $form['app_adstxt_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Contents of app-ads.txt'),
      '#default_value' => $config->get('app_content'),
      '#cols' => 60,
      '#rows' => 20,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('adstxt.settings');
    $config
      ->set('content', $form_state->getValue('adstxt_content'))
      ->set('app_content', $form_state->getValue('app_adstxt_content'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
