<?php

namespace Drupal\pizzahips\Normalizer;

use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Converts typed data objects to arrays.
 */
class CartNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = ProductVariationInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $context += [
      'account' => NULL,
    ];

    $attributes = [];
    foreach (TypedDataInternalPropertiesHelper::getNonInternalProperties($entity->getTypedData()) as $name => $field_items) {
      if ($field_items->access('view', $context['account'])) {
        $attributes[$name] = $this->serializer->normalize($field_items, $format, $context);
      }
    }
    if (!empty($attributes['field_product_variation_image'])) {
      $original_image = $entity->field_product_variation_image->entity->getFileUri();
      $style = \Drupal::entityTypeManager()
        ->getStorage('image_style')
        ->load('thumbnail');
      $destination = $style->buildUri($original_image);
      if (!file_exists($destination)) {
        $style->createDerivative($original_image, $destination);
      }
      $attributes['field_product_variation_image'] = $style->buildUrl($original_image);

    }
    return $attributes;
  }

}
