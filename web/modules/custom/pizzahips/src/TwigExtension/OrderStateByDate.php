<?php

namespace Drupal\pizzahips\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Custom twig functions.
 */
class OrderStateByDate extends AbstractExtension {

  /**
   * {@inheritDoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('get_filter', [$this, 'getFilter']),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFilter($varGet) {
    $key = \Drupal::request()->get($varGet);
    if (empty($key)) {
      return '';
    }
    return '&' . $varGet . '=' . $key;
  }

  /**
   * {@inheritDoc}
   */
  public function getName() {
    return 'pizzahips.OrderStateByDate';
  }

}
