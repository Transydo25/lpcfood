<?php

namespace Drupal\pizzahips\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "shop_order_goto_block",
 *   admin_label = @Translation("Shop goto order"),
 *   category = @Translation("Custom Cart block"),
 * )
 */
class ShopOrderGotoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $store_ids = [];
    if (!empty(array_intersect(['administrator'], $roles))) {
      $store_ids = \Drupal::entityQuery('node')
        ->condition('type', 'store_locator')
        ->execute();
    }
    else {
      $user = User::load($current_user->id());
      $storeRef = $user->field_store->entity;
      if (!empty($storeRef)) {
        $store_ids = [$storeRef->id()];
      }
    }
    $stores = Node::loadMultiple($store_ids);
    foreach ($stores as $store) {
      $url = Link::createFromRoute($store->getTitle(), 'view.orders.orders', ['arg_0' => $store->uuid()]);
      $items[] = [
        '#wrapper_attributes' => ['class' => ['list-group-item']],
        '#children' => $url->toString(),
      ];
    }
    if (!empty($items)) {
      return [
        [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#title' => $this->t('Shop list'),
          '#items' => $items,
          '#attributes' => ['class' => ['list-group']],
        ],
      ];
    }
    return NULL;
  }

}
