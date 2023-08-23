<?php

namespace Drupal\pizzahips\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Store Locator search form.
 */
class SendOrderMailForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'send_order_mail_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Recipient'),
      '#pattern' => "(?![_.-])((?![_.-][_.-])[a-zA-Z\d_.-]){0,63}[a-zA-Z\d]@((?!-)((?!--)[a-zA-Z\d-]){0,63}[a-zA-Z\d]\.){1,2}([a-zA-Z]{2,14}\.)?[a-zA-Z]{2,14}",
      '#placeholder' => $this->t('E-mail address'),
      '#description' => 'Ex: dupont@sfr.fr',
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
    if (!empty($to = $values["email"]) && !empty($values["message"])) {
      $user = User::load(\Drupal::currentUser()->id());
      $email = $user->getEmail();
      $name = $user->getDisplayName();
      $renderer = [
        '#type'     => 'inline_template',
        '#template' => $values["message"],
      ];
      $key = 'order-send-mail';
      $mailManager = \Drupal::service('plugin.manager.mail');
      $message = [
        'id' => $key,
        'headers' => [
          'Content-type' => 'text/html; charset=UTF-8; format=flowed; delsp=yes',
          'Reply-to' => $name . '<' . $email . '>',
          'Content-Transfer-Encoding' => '8Bit',
          'MIME-Version' => '1.0',
        ],
        'subject' => $this->t('Message from') . \Drupal::config('system.site')->get('name'),
        'to' => $to,
        'body' => \Drupal::service('renderer')->render($renderer),
      ];
      $mailManager->getInstance(['module' => 'pizzahips', 'key' => $key])
        ->mail($message);
    }
  }

}
