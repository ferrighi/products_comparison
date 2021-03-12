<?php
/*
 *
 * @file
 * Contains \Drupal\products_comparison\Form\ProductsComparisonConfigurationForm
 *
 * Form for Products comparison Admin Configuration
 *
 **/
namespace Drupal\products_comparison\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/*
 *  * Class ConfigurationForm.
 *
 *  {@inheritdoc}
 *
 *   */
class ProductsComparisonConfigurationForm extends ConfigFormBase {

  /*
   * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'products_comparison.configuration',
      ];
  }

  /*
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'products_comparison.admin_config_form';
  }

  /*
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('products_comparison.configuration');
    $form = [];


    $form['helptext'] = [
      '#type'          => 'text_format',
      '#title'         => $this->t('Help markup text'),
      '#format'        => $config->get('helptext')['format'],
      '#default_value' => $config->get('helptext')['value'],
    ];

    //$form['#attached']['library'][] = 'products_comparison/products_comparison';
    return parent::buildForm($form, $form_state);
 }

  /*
   * {@inheritdoc}
   *
   * NOTE: Implement form validation here
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /*
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /**
     * Save the configuration
    */
    $values = $form_state->getValues();
    $this->configFactory->getEditable('products_comparison.configuration')
      ->set('helptext', $values['helptext'])
      ->save();
    parent::submitForm($form, $form_state);
  }
}
