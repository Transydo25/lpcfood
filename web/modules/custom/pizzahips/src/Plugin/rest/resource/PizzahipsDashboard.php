<?php

namespace Drupal\pizzahips\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the dashboard data Resource.
 *
 * @RestResource(
 *   id = "pizzahips_dashboard",
 *   label = @Translation("Commerce dashboard data"),
 *   uri_paths = {
 *     "canonical" = "/pizzahips_dashboard/data/{store_id}"
 *   }
 * )
 */
class PizzahipsDashboard extends ResourceBase {

  /**
   * The current active database's master connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * An entity type manager service instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The default commerce store entity.
   *
   * @var \Drupal\commerce_store\Entity\StoreInterface
   */
  protected $defaultStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->database = $container->get('database');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->defaultStore = $container->get('commerce_store.default_store_resolver')
      ->resolve();
    return $instance;
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The rest resource response.
   */
  public function get($store_uuid = 0) {
    $store_id = $this->getStoreId($store_uuid);
    $sales = $this->getSalesData($store_id);
    $top_products = $this->getTopProducts($store_id);
    $currency_code = $this->defaultStore->getDefaultCurrencyCode();
    $response = [
      'sales' => $sales,
      'topProducts' => $top_products,
      'currencyCode' => $currency_code,
    ];
    return new ResourceResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  public function getSalesData($store_id = 0) {
    $order_storage = $this->entityTypeManager->getStorage('commerce_order');
    $query = $order_storage->getQuery();
    $query->condition('state', 'completed');
    $query->condition('placed', strtotime('now -31 days'), '>=');
    if ($store_id) {
      $query->condition('field_store_selected', $store_id);
    }
    $query->accessCheck(FALSE);
    $order_ids = $query->execute();
    $order_storage = $order_storage->loadMultiple($order_ids);
    $totals = [
      'today' => 0,
      'yesterday' => 0,
      'prevYesterday' => 0,
      'week' => 0,
      'prevWeek' => 0,
      'lineChart' => [],
    ];
    $day = strtotime('now -31 days');
    do {
      $totals['lineChart'][date('d-m', $day)] = 0;
      $day++;
    } while ($day < time());

    foreach ($order_storage as $order) {
      if (date('Y-m-d', $order->getPlacedTime()) == date('Y-m-d')) {
        $totals['today'] += $order->getTotalPrice()->getNumber();
      }
      if (date('Y-m-d', $order->getPlacedTime()) == date('Y-m-d', strtotime('yesterday'))) {
        $totals['yesterday'] += $order->getTotalPrice()->getNumber();
      }
      if (date('Y-m-d', $order->getPlacedTime()) == date('Y-m-d', strtotime('now -2 days'))) {
        $totals['prevYesterday'] += $order->getTotalPrice()->getNumber();
      }
      if ($order->getPlacedTime() > strtotime('now -7 days')) {
        $totals['week'] += $order->getTotalPrice()->getNumber();
      }
      else {
        $totals['prevWeek'] += $order->getTotalPrice()->getNumber();
      }
      $totals['lineChart'][date('d-m', $order->getPlacedTime())] += $order->getTotalPrice()
        ->getNumber();
    }

    $today_percentage = 0;
    if ($totals['yesterday'] !== 0) {
      $today_percentage = round(abs($totals['today'] - $totals['yesterday']) / $totals['yesterday'] * 100, 2);
    }
    $yesterday_percentage = 0;
    if ($totals['prevYesterday'] !== 0) {
      $yesterday_percentage = round(abs($totals['yesterday'] - $totals['prevYesterday']) / $totals['prevYesterday'] * 100, 2);
    }
    $week_percentage = 0;
    if ($totals['prevWeek'] !== 0) {
      $week_percentage = round(abs($totals['week'] - $totals['prevWeek']) / $totals['prevWeek'] * 100, 2);
    }
    return [
      'today' => [
        'amount' => $totals['today'],
        'changeIndicator' => ($totals['today'] > $totals['yesterday']) ? 'increased' : 'decreased',
        'changePercentage' => $today_percentage,
      ],
      'yesterday' => [
        'amount' => $totals['yesterday'],
        'changeIndicator' => ($totals['yesterday'] > $totals['prevYesterday']) ? 'increased' : 'decreased',
        'changePercentage' => $yesterday_percentage,
      ],
      'week' => [
        'amount' => $totals['week'],
        'changeIndicator' => ($totals['week'] > $totals['prevWeek']) ? 'increased' : 'decreased',
        'changePercentage' => $week_percentage,
      ],
      'lineChart' => $totals['lineChart'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTopProducts($store_id = 0) {
    $query = $this->database->select('commerce_order', 'commerce_order');
    $query->addJoin('LEFT', 'commerce_order_item', 'order_item', 'order_item.order_id = commerce_order.order_id');
    $query->fields('order_item', ['purchased_entity','title']);
    $query->addExpression('ROUND(SUM(order_item.total_price__number), 2)', 'total');
    $query->addExpression('ROUND(SUM(quantity), 0)', 'purchases');
    $query->groupBy('order_item.purchased_entity')->groupBy('order_item.title');
    $query->condition('commerce_order.placed', strtotime('now -31 days'), '>=');
    if ($store_id) {
      $query->addJoin('LEFT', 'commerce_order__field_store_selected', 'store_selected', 'store_selected.entity_id = commerce_order.order_id');
      $query->condition('store_selected.field_store_selected_target_id', $store_id);
    }
    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }


  /**
   * {@inheritdoc}
   */
  private function getStoreId(int $uuid) {
    $store_id = 0;
    $entity = \Drupal::service('entity.repository')->loadEntityByUuid('node', $uuid);
    if($entity){
      return $entity->id();
    }
    return $store_id;
  }

}
