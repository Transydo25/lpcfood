<?php

namespace Drupal\pizzahips\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Block store location.
 *
 * @Block(
 *   id = "search_store_locator_block",
 *   admin_label = @Translation("Custom Search block"),
 *   category = @Translation("Custom Search block")
 * )
 */
class SearchStoreLocatorBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()
      ->getForm('Drupal\pizzahips\Form\SearchStoreLocatorForm');
    return $form;
  }

}
