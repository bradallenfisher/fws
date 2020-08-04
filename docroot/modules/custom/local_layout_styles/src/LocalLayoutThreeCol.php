<?php

namespace Drupal\local_layout_styles;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class LocalLayoutThreeCol extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'sizing' => 'Default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();

    $form['column_widths'] = [
      '#type' => 'select',
      '#title' => $this->t('Column Widths'),
      '#options' => [
        'layout--threecol-section--25-50-25' => $this->t('25%/50%/25%'),
        'layout--threecol-section--33-34-33' => $this->t('33%/34%/33%'),
        'layout--threecol-section--25-25-50' => $this->t('25%/25%/50%'),
        'layout--threecol-section--50-25-25' => $this->t('50%/25%/25%'),
      ],

      '#default_value' => $configuration['column_widths'],
    ];
    $form['sizing'] = [
      '#type' => 'select',
      '#empty_option' => " -- Select Sizing -- ",
      '#title' => $this->t('Sizing'),
      '#options' => [
        'full' => $this->t('Full'),
        'box' => $this->t('Box'),
      ],
      '#default_value' => $configuration['sizing'],
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label'),
      '#default_value' => $this->configuration['label'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // any additional form validation that is required
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['column_widths'] = $form_state->getValue('column_widths');
    $this->configuration['sizing'] = $form_state->getValue('sizing');
    $this->configuration['label'] = $form_state->getValue('label');
  }

}