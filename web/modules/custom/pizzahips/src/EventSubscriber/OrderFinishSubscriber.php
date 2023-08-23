<?php
namespace Drupal\pizzahips\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends an email when the order transitions to Fulfillment.
 */
class OrderFinishSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a new OrderFulfillmentSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    MailManagerInterface $mail_manager
  ) {
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.finish.post_transition' => ['sendEmailReady', -100],
      'commerce_order.cancel.post_transition' => ['sendEmailCancel', -100],
    ];
    return $events;
  }

  /**
   * Sends the email.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function sendEmailReady(WorkflowTransitionEvent $event) {
    // Create the email notification prepared.
    $term_name = 'send mail order prepared';
    $this->sendMailWithTemplate($event, $term_name);
  }

  /**
   * Sends the email.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function sendEmailCancel(WorkflowTransitionEvent $event) {
    // Create the email cancel.
    $term_name = 'send mail order cancel';
    $this->sendMailWithTemplate($event, $term_name);
  }

  protected function sendMailWithTemplate($event, $term_name){
    $order = $event->getEntity();
    // Set the language that will be used in translations.
    if ($customer = $order->getCustomer()) {
      $langcode = $customer->getPreferredLangcode();
    }
    else {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }
    $service =  \Drupal::service('order.context');
    $context = $service->getOrder($order->id(), $langcode);
    $context['key'] = 'order-customer-prepared';
    $message = $service->getTemplateTerm($term_name, $context, $langcode);
    if (!empty($message)) {
      $this->mailManager->getInstance(['module' => 'pizzahips', 'key' => $context['key']])
        ->mail($message);
    }
  }
}
