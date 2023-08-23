<?php

namespace Drupal\pizzahips\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Custom twig functions.
 */
class ConvertPrice extends AbstractExtension {

  /**
   * {@inheritDoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('convert_price', [$this, 'convertPriceFormat']),

    ];
  }

  /**
   * {@inheritDoc}
   */
  public function convertPriceFormat($price) {
    $price = (string) $price;
    $fmt = new \NumberFormatter('vi_VI', \NumberFormatter::CURRENCY);
    return $fmt->formatCurrency($price, "VND");
  }

  /**
   * {@inheritDoc}
   */
  public function getName() {
    return 'pizzahips.ConvertPrice';
  }

}
