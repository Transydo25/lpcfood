<?php

namespace Drupal\pizzahips\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "cart_order_items_block",
 *   admin_label = @Translation("Order summary"),
 *   category = @Translation("Custom Cart block"),
 * )
 */
class CartOrderItemsSummaryBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_store = \Drupal::service('commerce_store.current_store');
    $store = $current_store->getStore();
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $cart = $cart_provider->getCart("default", $store);
    if (!empty($cart)) {
      $order_id = $cart->id();
      $txtOrder = $this->t('Order');

      return [
        [
          '#markup' => '<h2>' . $txtOrder . ' N° ' . $order_id . '</h2>',
        ],
        [
          '#theme' => 'commerce_checkout_order_summary',
          '#order_entity' => $cart,
          '#cache' => ['max-age' => 0],
        ],
      ];
    }
    return NULL;
  }

}
