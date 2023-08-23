<?php

namespace Drupal\pizzahips\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "cart_related_products_block",
 *   admin_label = @Translation("You would also like"),
 *   category = @Translation("Custom Cart block"),
 * )
 */
class CartRelatedProductsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_store = \Drupal::service('commerce_store.current_store');
    $store = $current_store->getStore();
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $cart = $cart_provider->getCart("default", $store);
    $argv = [];
    if (!empty($cart) && $cart->hasItems()) {
      foreach ($cart->getItems() as $order_item) {
        if (empty($purchased = $order_item->getPurchasedEntity())) {
          continue;
        }
        $argv[] = $purchased->id();
      }
    }
    return [
      '#type' => 'view',
      '#name' => 'related_products',
      '#display_id' => 'related_products',
      '#arguments' => !empty($argv) ? [implode('+', $argv)] : '',
      '#embed' => TRUE,
    ];
  }

}
