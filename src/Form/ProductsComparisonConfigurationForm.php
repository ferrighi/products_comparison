<?php

namespace Drupal\products_comparison\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for the products comparison module.
 *
 * {@inheritdoc}
 */
class ProductsComparisonConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'products_comparison.configuration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'products_comparison.admin_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('products_comparison.configuration');
    $form = [];

    $form['defzoom'] = [
      '#type' => 'number',
      '#title' => 'Enter default initial map zoom',
      '#default_value' => $config->get('defzoom'),
    ];

    $form['lon'] = [
      '#type' => 'number',
      '#title' => 'Enter default longitude center map point (EPSG:4326)',
      '#default_value' => $config->get('lon'),
    ];

    $form['lat'] = [
      '#type' => 'number',
      '#title' => 'Enter default latitude center map point (EPSG:4326)',
      '#default_value' => $config->get('lat'),
    ];

    $form['rows'] = [
      '#type' => 'number',
      '#title' => 'Enter the number of date rows to return in list',
      '#default_value' => $config->get('rows'),
    ];

    $form['helptext'] = [
      '#type'          => 'text_format',
      '#title'         => $this->t('Help markup text'),
      '#format'        => $config->get('helptext')['format'],
      '#default_value' => $config->get('helptext')['value'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: Implement form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /* Save the configuration */
    $values = $form_state->getValues();
    $this->configFactory->getEditable('products_comparison.configuration')
      ->set('helptext', $values['helptext'])
      ->set('defzoom', $values['defzoom'])
      ->set('lat', $values['lat'])
      ->set('lon', $values['lon'])
      ->set('rows', $values['rows'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
