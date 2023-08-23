<?php

namespace Drupal\pizzahips\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an example form.
 */
class CartQrForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cart_qr';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $product_types = \Drupal\commerce_product\Entity\ProductType::loadMultiple();
    $header = [
      'color' => $this
        ->t('Color'),
      'shape' => $this
        ->t('Shape'),
    ];
    $options = [
      1 => [
        'color' => 'Red',
        'shape' => 'Triangle',
      ],
      2 => [
        'color' => 'Green',
        'shape' => 'Square',
      ],
      // Prevent users from selecting a row by adding a '#disabled' property set
      // to TRUE.
      3 => [
        'color' => 'Blue',
        'shape' => 'Hexagon',
      ],
    ];
    $form['table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this
        ->t('No shapes found'),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $a =123;
  }

}
