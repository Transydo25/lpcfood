<?php

namespace Drupal\pizzahips\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Store Locator search form.
 */
class SendOrderSmsForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'send_order_sms_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Recipient'),
      '#pattern' => '^(0[6-7]{1}[0-9]{8}|\+?33[6-7][0-9]{8})$',
      '#placeholder' => $this->t('Mobile number'),
      '#description' => 'Ex: 0612345678',
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#placeholder' => $this->t('Type message to customer'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values["phone"]) && !empty($values["message"])) {
      $uid = \Drupal::currentUser()->id();
      $renderer = [
        '#type'     => 'inline_template',
        '#template' => $values["message"],
      ];
      $message = \Drupal::service('renderer')->render($renderer);
      $sms = \Drupal::entityTypeManager()->getStorage('sms_message')->create([
        'type' => 'sms_message',
        'number' => $values["phone"],
        'message' => [
          'value' => $message,
        ],
        'uid' => $uid,
      ]);
      $sms->save();
    }
  }

}
