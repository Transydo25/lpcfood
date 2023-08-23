<?php

namespace Drupal\pizzahips\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Store Locator search form.
 */
class SearchStoreLocatorForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'search_store_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#action'] = '/search-map';
    $form['#attributes']['class'] = ['container'];
    $form['#attached']['library'][] = 'pizzahips/pizzahips_global';
    $form['search'] = [
      '#type' => 'search',
      '#title' => '',
      '#title_display' => 'invisible',
      '#attributes' => [
        'placeholder' => $this->t('ZIP code, City...'),
      ],
      '#size' => NULL,
      '#wrapper_attributes' => ['class' => ['col-auto']],
      '#default_value' => \Drupal::request()->query->get('q'),
    ];

    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name == 'view.notre_carte.store_location') {
      $form['search']['#field_suffix'] = '<i class="icon_get_location icon_suffix bi bi-cursor-fill"></i>';
      $form['actions']['submit'] = [
        '#type' => 'hidden',
        '#value' => t('Search'),
        '#prefix' => '<span type="submit" class="submit-search input-group-text"><i class="bi bi-search"></i>',
        '#suffix' => '</span>',
      ];
    }
    else {
      // If homepage.
      $form['search']['#field_prefix'] = '<i class="icon_get_location icon_prefix bi bi-geo-alt-fill"></i>';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Search'),
        '#button_type' => 'primary',
        '#wrapper_attributes' => ['class' => ['col-auto']],
      ];
    }

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
