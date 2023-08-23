<?php

namespace Drupal\pizzahips\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "checkout_pane_reservation",
 *   label = @Translation("Reservation"),
 * )
 */
class ReservationPane extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->buildForm($this->order, $pane_form, $form_state);
    // Hide fields.
    $pane_form["billing_profile"]["#access"] = FALSE;
    $pane_form["order_items"]["#access"] = FALSE;
    $pane_form["cart"]["#access"] = FALSE;
    $pane_form["field_indication"]["#attributes"]["class"][] = 'js-hide';
    $pane_form["field_store_selected"]["#attributes"]["class"] = 'js-hide';
    $pane_form["field_field_indication"]["#attributes"]["class"] = 'js-hide';
    $pane_form["field_reason_cancel"]["#attributes"]["class"] = 'js-hide';
    $pane_form["field_pickup_date"]["#attributes"]["class"] = 'js-hide';
    $pane_form["field_oder_type"]["#attributes"]["class"] = 'js-hide';
    $pane_form["coupons"]["#attributes"]["class"] ='js-hide';
//    $pane_form['coupon'] = ['#type' => 'commerce_coupon_redemption_form', '#title' => t('Coupon code'),'#order_id' => $this->order->id(),];
    $complete_form["actions"]["#attributes"]["class"][] = 'js-hide';

    // Add JS.
    $pane_form['#attached']['library'][] = 'pizzahips/pickup';
    $url = Url::fromRoute('pizzahips.save_history_search_map');
    $pane_form['#attached']['drupalSettings']['pizzahips']['save_history_search_map'] = $url->toString();

    $storeSelected = \Drupal::request()->cookies->get('historySearchMap') ?? '';
    $address = \Drupal::request()->cookies->get('nameLocationMap') ?? '';

    if (!empty($storeSelected)) {
      $pane_form["field_store_selected"]["widget"]["target_id"]["#default_value"] = Node::load($storeSelected);
    }
    // Get store list.
    $listStore = views_embed_view('list_store', 'api_location');
    $data = \Drupal::service('renderer')->render($listStore);
    $data = str_replace("\u0026#039;","'", $data);
    $stores = json_decode($data, TRUE);


    // Render time summary.
    $date = new DrupalDateTime('now');
    $defaultTime = '11:00';
   $plus3Hours = $date->format('H');
   if ($plus3Hours <= 9 || $plus3Hours >= 17) {
     $plus3Hours = '';
   } else {
     $defaultTime = $plus3Hours . ':00';
     $plus3Hours = $plus3Hours . ':00 - ' . $plus3Hours . ':30';
   }
    // if today is Saturday and over 17h;
   if($date->format('w') == 6 && empty($plus3Hours)){
     $interval = new \DateInterval('P2D');
     $date->add($interval);
   }else {
     // If today is sunday.
     if (!$date->format('w') || empty($plus3Hours)) {
       $interval = new \DateInterval('P1D');
       $date->add($interval);
     }
   }
   $defaultDay = strtotime($date->format('Y-m-d ' . $defaultTime . ':00'));
   $pane_form["field_pickup_date"]["widget"][0]["value"]["#default_value"] = DrupalDateTime::createFromTimestamp($defaultDay);

    // Generate UI.
    $pane_form["pickup"] = [
      '#theme' => 'reservation',
      '#order_id' => $this->order->get('order_id')->value,
      '#storeSelected' => $storeSelected,
      '#stores' => $stores,
      '#datePickup' => $this->datePickup(),
      '#timePickup' => $this->timePickup(8, 18),
      '#address' => strip_tags($address, '<span> <br>'),
      '#date' => $date->format('D d/m/Y'),
      '#time' => $defaultTime,
//      '#coupon' => $pane_form['coupon'],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    if ($order_comment = $this->order->getData('order_comment')) {
      return [
        '#plain_text' => $order_comment,
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->extractFormValues($this->order, $pane_form, $form_state);
    $form_display->validateFormValues($this->order, $pane_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->extractFormValues($this->order, $pane_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  private function timePickup($timeStart = [6,17], $timeEnd = [13,21]) {
    $timePickup = [];
    foreach (range(8, 17) as $hour) {
      $hour = sprintf("%02d", $hour);
      $time = "$hour:00";
      $a = strtotime($time);
      $timePickup[] = [
        'time' => $time . ':00',
        'text' => $time . ' - ' . date("H:i", strtotime('+30 minutes', strtotime($time))),
      ];
      $time = "$hour:30";
      $timePickup[] = [
        'time' => $time . ':00',
        'text' => $time . ' - ' . date("H:i", strtotime('+30 minutes', strtotime($time))),
      ];

    }
    return $timePickup;
  }

  /**
   * {@inheritdoc}
   */
  private function datePickup() {
    $date = new DrupalDateTime('now');
    // Render next 3days but not count on sunday.
    $datePickup = [];
    $today = new DrupalDateTime("now");
    $inOrderTime = $today->format('H') < 21;
    if ($date->format('w') && $inOrderTime) {
      $datePickup[] = [
        'date' => $date->format('Y-m-d'),
        'text' => "<h5>" . $this->t('Today') . "</h5> " . $date->format('d F Y'),
      ];
    }
    foreach (range(1, 5) as $day) {
      $date->modify('+1 day');
      if (!$date->format('w')) {
        continue;
      }
      $datePickup[] = [
        'date' => $date->format('Y-m-d'),
        'text' => "<h5>" . $date->format('l') . "</h5> " . $date->format('d F Y'),
      ];
    }
    $datePickup = array_slice($datePickup, 0, 4);
    $date->modify('+1 day');

    $datePickup[] = [
      'date' => '',
      'text' => "<h5>" . $this->t('Another') . "</h5><input min='" . $date->format('Y-m-d') . "' id='dateAnother' type='date' class='form-control dateAnother'/>",
    ];
    return $datePickup;
  }

}
