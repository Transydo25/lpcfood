<?php

namespace Drupal\pizzahips\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Store Locator search form.
 */
class CancelOrderForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'cancel_order_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $field_name = 'field_reason_cancel';
    $entity_type = "commerce_order";
    $bundle = 'default';
    $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
      ->getFieldDefinitions($entity_type, $bundle);
    $field_definition = $bundle_fields[$field_name];
    $allowed_values = $field_definition->getSetting('allowed_values');
    $label = $field_definition->getLabel();
    $description = $field_definition->getDescription();
    $form[$field_name] = [
      '#type' => 'checkboxes',
      '#options' => $allowed_values,
      '#description' => $description,
      '#title' => $label,
    ];
    $form['order_id'] = [
      '#type' => 'hidden',
      '#title' => 'Order id',
      '#attributes' => [
        'class' => ['order-cancel-id']
      ],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['order_id'])) {
      $order = \Drupal\commerce_order\Entity\Order::load($values['order_id']);
      $reasons =  array_filter($values['field_reason_cancel']);
      foreach($reasons as $reason){
        $order->field_reason_cancel->appendItem($reason);
      }
      $order_state = $order->getState();
      $order_state->applyTransitionById('cancel');
      $order->save();
    }
  }

}
