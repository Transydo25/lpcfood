<?php

namespace Drupal\pizzahips\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class NotificationFirebaseController.
 */
class MapController extends ControllerBase {

  /**
   * {@inheritDoc}
   */
  public function saveHistorySearchMap() {
    $idLocation = \Drupal::request()->request->get('idLocation');
    $nameLocation = trim(\Drupal::request()->request->get('nameLocation'));

    if (!empty($idLocation)) {
      $time = time() + 365 * 24 * 60 * 60;
      setcookie('historySearchMap', $idLocation, $time, "/");
      setcookie('nameLocationMap', $nameLocation, $time, "/");
    }

    return ['#markup' => t('Store location archive')];
  }

  /**
   * {@inheritDoc}
   */
  public function searchMap() {
    $search = \Drupal::request()->request->get('search');
    $listData = \Drupal::service('address_suggestion.query_services')
      ->getData('node', 'store_locator', 'field_address', $search);
    $path = Url::fromRoute('view.notre_carte.store_location', [
      'arg_0' => $listData[0]["location"]["latitude"] . ',' . $listData[0]["location"]["longitude"] . '<=20km',
    ], [
      'query' => ['q' => $search],
    ])
      ->toString();

    $response = new RedirectResponse($path);
    $response->send();

    return ['#markup' => t('Đã lưu lịch sử tìm kiếm')];
  }

}
