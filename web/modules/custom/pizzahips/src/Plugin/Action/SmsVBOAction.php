<?php

namespace Drupal\pizzahips\Plugin\Action;
use Drupal\sms_message\Plugin\Action\SmsSendVBOAction;
use Drupal\user\Entity\User;

/**
 * Create sms VBO.
 *
 * @Action(
 *   id = "sms2_vbo_action",
 *   label = @Translation("Send SMS VBO Action"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class SmsVBOAction extends SmsSendVBOAction {
  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity) {
      $config = $this->getConfiguration();

      // Get the billing profile from the order.
      $billing_profile = $entity->getBillingProfile();
      $phone = $billing_profile->get($config['field_telephone'])->getValue()[0]["value"];

      if (empty($phone) || empty($config['template'])) {
        return FALSE;
      }
      $view = $this->view;
      $previous = $view->display_handler->getFieldLabels();
      $context = [];
      foreach ($previous as $fieldName => $field) {
        $entityField = $view->field[$fieldName];
        $key = array_search($entity->id(), array_column($view->result, 'nid'));
        $context[$fieldName] = $entityField->advancedRender($view->result[$key]);
      }
      $renderer = [
        '#type' => 'inline_template',
        '#template' => $config['template'],
        '#context' => $context,
      ];
      $message = strip_tags(\Drupal::service('renderer')->render($renderer));
      $sms = \Drupal::entityTypeManager()->getStorage('sms_message')->create([
        'type' => 'sms_message',
        'number' => $phone,
        'message' => [
          'value' => trim($message),
        ],
        'uid' => User::load(\Drupal::currentUser()->id()),
      ]);
      $sms->save();
    }
  }
}
