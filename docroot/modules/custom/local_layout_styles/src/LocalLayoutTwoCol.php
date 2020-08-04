<?php

namespace Drupal\local_layout_styles;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class LocalLayoutTwoCol extends LayoutDefault implements PluginFormInterface {

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
        'layout--twocol-section--50-50' => $this->t('50%/50%'),
        'layout--twocol-section--33-67' => $this->t('33%/67%'),
        'layout--twocol-section--67-33' => $this->t('67%/33%'),
        'layout--twocol-section--25-75' => $this->t('25%/75%'),
        'layout--twocol-section--75-25' => $this->t('75%/25%'),
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

    $form['border_bottom'] = [
      '#type' => 'select',
      '#empty_option' => "No",
      '#title' => $this->t('Border Bottom'),
      '#options' => [
        'border_bottom' => $this->t('Yes'),
      ],
      '#default_value' => $configuration['border_bottom'],
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
    $this->configuration['border_bottom'] = $form_state->getValue('border_bottom');
    $this->configuration['label'] = $form_state->getValue('label');
  }

}