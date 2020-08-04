<?php

namespace Drupal\local_layout_styles;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

class LocalLayout extends LayoutDefault implements PluginFormInterface {

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
    $form['sizing'] = [
      '#type' => 'select',
      '#empty_option' => " -- Select Sizing -- ",
      '#title' => $this->t('Sizing'),
      '#options' => [
        'full' => $this->t('Full'),
        'box' => $this->t('Box'),
        'newclass' => $this->t('New One To Test on Old one'),
      ],
      '#default_value' => $configuration['sizing'],
    ];
    $form['background_color'] = [
      '#type' => 'select',
      '#empty_option' => " -- NONE -- ",
      '#title' => $this->t('Background Color'),
      '#options' => [
        'dark' => $this->t('Dark'),
        'medium' => $this->t('Medium'),
        'brand' => $this->t('Brand'),
      ],
      '#default_value' => $configuration['background_color'],
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
    $this->configuration['label'] = $form_state->getValue('label');
    $this->configuration['sizing'] = $form_state->getValue('sizing');
    $this->configuration['background_color'] = $form_state->getValue('background_color');

  }

}