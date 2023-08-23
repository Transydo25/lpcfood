<?php

namespace Drupal\pizzahips;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\commerce_order\Entity\Order;
use function Doctrine\Common\Collections\Expr\getValue;

class OrderContext {

  /**
   * Get variables allow in order.
   * {@inheritDoc}
   */
  public function getOrder($order_id, $langcode = '') {
    $order = Order::load($order_id);
    $billing_profile = $order->getBillingProfile();
    $email = $billing_profile->field_email->value;
    $fullName = $billing_profile->address->given_name;
    $phone = $billing_profile->field_telephone->value;
    $store_id = $order->field_store_selected->target_id;
    $store = Node::load($store_id);
    $order_id = $order->id();
    $indication = $order->field_indication->value;
    $address_to = $order->field_address_to->value;
    $time7 = date('Y-m-d H:i:s',strtotime('+7 hour',strtotime($order->field_pickup_date->value)));
    $order_date = [
      '#type' => 'html_tag',
      '#tag' => 'time',
      '#attributes' => [
        'datetime' => $time7,
      ],
      '#value' => \Drupal::service('date.formatter')
        ->format(strtotime($time7), 'date_text'),
    ];
    $day = Date('d/m/Y', strtotime($order->field_pickup_date->value));
    $time = Date('H:i', strtotime($order->field_pickup_date->value));
    $order_number = $order->getOrderNumber();
    $order_reason_cancel = '';
    if(!empty($order->field_reason_cancel) && !empty($order->field_reason_cancel->value)){
      $cancelFieldDefinition = $order->getFieldDefinition('field_reason_cancel');
      $allowed_values = $cancelFieldDefinition->getSetting('allowed_values');
      $order_reason_cancel = $allowed_values[$order->field_reason_cancel->value];
    }
    $renderService = \Drupal::service('renderer');
    $currency_formatter = \Drupal::service('commerce_price.currency_formatter');
    $total = $currency_formatter->format($order->getTotalPrice()
      ->getNumber(), $order->getTotalPrice()->getCurrencyCode());
    $subTotal = $currency_formatter->format($order->getSubtotalPrice()
      ->getNumber(), $order->getSubtotalPrice()->getCurrencyCode());
    // Render order Items.
    $rows = [];
    foreach ($order->getItems() as $key => $order_item) {
      $product_variation = $order_item->getPurchasedEntity();
      $formatted_price = $currency_formatter->format($order_item->getTotalPrice()
        ->getNumber(), $order_item->getTotalPrice()->getCurrencyCode());
      $formatted_unitprice =  $currency_formatter->format($order_item->getUnitPrice()->getNumber(), $order_item->getUnitPrice()->getCurrencyCode());
      $rows[] = [
        $product_variation->getTitle(),
        $order_item->getQuantity(),
        $formatted_unitprice,
        $formatted_price,
      ];
    }
    $itemHeader = [
      ['data' => t('Product name'), 'align' => 'left'],
      ['data' => t('Quantity'), 'align' => 'left'],
      ['data' => t('Unit price'), 'align' => 'left'],
      ['data' => t('Amount'), 'align' => 'left']
    ];
//    $rows[] = ['', '', t('Total'), $total];
    $rows[] = ['', '', ['data' => t('Total price'), 'style' => 'font-weight:bold'], ['data' => $total, 'style' => 'font-weight:bold']];
    $orderSummary = [
      '#type' => 'table',
      '#header' => $itemHeader,
      '#rows' => $rows,
      '#attributes' => [
        'width' => "100%",
        'cellpadding' => 0,
        'cellspacing' => 0,
        'style' => 'border-collapse:separate!important;border-color:#e4e2e2;border-radius:4px;',
      ],
    ];
    $order_summary = $renderService->render($orderSummary);
    $store_location = $store->field_address;
    if (!empty($store_location)) {
      $address = current($store_location->getValue());
      $render = [
        '#type' => 'html_tag',
        '#tag' => 'address',
        '#value' => $address["address_line1"],
      ];
      $store_location = $renderService->render($render);
    }
    $store_phone = $store->field_telephone;
    if (!empty($store_phone)) {
      $render = [
        '#type' => 'link',
        '#title' => $store_phone->value,
        '#url' => Url::fromUri('tel:' . $store_phone->value),
        '#options' => ['external' => TRUE],
      ];
      $store_phone = $renderService->render($render);
    }
    return [
      'customer_name' => $fullName,
      'customer_phone' => $phone,
      'customer_email' => $email,
      'store_location' => $store_location,
      'store_phone' => $store_phone,
      'store_id' => $store_id,
      'order_id' => $order_id,
      'order_number' => $order_number,
      'order_summary' => $order_summary,
      'order_date' => $order_date,
      'order_day' => $day,
      'order_time' => $time,
      'order_total' => $total,
      'order_subtotal' => $subTotal,
      'order_reason_cancel' => $order_reason_cancel,
      'indication' => $indication,
      'address_to' => $address_to,
    ];
  }

  /**
   * Get template by taxonomy name.
   * {@inheritDoc}
   */
  public function getTemplateTerm($term_name, $context, $langcode = '') {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $term_name]);
    $term = reset($term);
    if (!empty($term)) {
      $renderer = [
        '#type' => 'inline_template',
        '#template' => $term->field_subject->value,
        '#context' => $context,
      ];
      $subject = \Drupal::service('renderer')->render($renderer);
      $renderer = [
        '#type' => 'inline_template',
        '#template' => $term->getDescription(),
        '#context' => $context,
      ];
      $body = \Drupal::service('renderer')->render($renderer);
      $name = \Drupal::config('system.site')->get('name');
      $email = \Drupal::config('system.site')->get('mail');
      return [
        'id' => $context['key'],
        'headers' => [
          'Content-type' => 'text/html; charset=UTF-8; format=flowed; delsp=yes',
          'Reply-to' => $name . '<' . $email . '>',
          'Content-Transfer-Encoding' => '8Bit',
          'MIME-Version' => '1.0',
        ],
        'to' => $context['customer_name'] . '<' . $context['customer_email'] . '>',
        'subject' => $subject,
        'body' => $body,
      ];
    }
    return [];
  }
}
