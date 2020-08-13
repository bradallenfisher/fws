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
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label'),
      '#default_value' => $this->configuration['label'],
    ];
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
    $form['padding'] = [
      '#type' => 'select',
      '#empty_option' => " -- Select Padding -- ",
      '#title' => $this->t('Top/Bottom Padding'),
      '#options' => [
        'pt10 pb10' => $this->t('10'),
        'pt20 pb20' => $this->t('20'),
        'pt30 pb30' => $this->t('30'),
      ],
      '#default_value' => $configuration['padding'],
    ];
    $form['margin_top'] = [
      '#type' => 'select',
      '#title' => $this->t('Top Margin'),
      '#options' => [
        'mt0' => $this->t('0'),
        'mt10' => $this->t('10'),
        'mt20' => $this->t('20'),
        'mt30' => $this->t('30'),
      ],
      '#default_value' => $configuration['margin_top'],
    ];
    $form['margin_bottom'] = [
      '#type' => 'select',
      '#title' => $this->t('Bottom Margin'),
      '#options' => [
        'mb0' => $this->t('0'),
        'mb10' => $this->t('10'),
        'mb20' => $this->t('20'),
        'mb30' => $this->t('30'),
      ],
      '#default_value' => $configuration['margin_bottom'],
    ];
    $form['background_color'] = [
      '#type' => 'select',
      '#empty_option' => " -- NONE -- ",
      '#title' => $this->t('Background Color'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
        'brand' => $this->t('Brand'),
      ],
      '#default_value' => $configuration['background_color'],
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
    $this->configuration['background_color'] = $form_state->getValue('background_color');
    $this->configuration['padding'] = $form_state->getValue('padding');
    $this->configuration['margin_top'] = $form_state->getValue('margin_top');
    $this->configuration['margin_bottom'] = $form_state->getValue('margin_bottom');
    $this->configuration['column_widths'] = $form_state->getValue('column_widths');
    $this->configuration['sizing'] = $form_state->getValue('sizing');
  }

}