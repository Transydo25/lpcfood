<?php

namespace Drupal\pizzahips\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\user\Entity\User;

/**
 * Create send email VBO.
 *
 * @Action(
 *   id = "email_vbo_action",
 *   label = @Translation("Send email with taxonomy template"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class EmailSendVBOAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface {

  use StringTranslationTrait;

  public function buildPreConfigurationForm(array $element, array $values, FormStateInterface $form_state): array {
    $bundle_info = \Drupal::service('entity_type.bundle.info');
    $entity_types = $bundle_info->getBundleInfo('taxonomy_term');
    foreach ($entity_types as $voc => $label) {
      $entity_types[$voc] = $label['label'];
    }
    $element['term_template'] = [
      '#title' => $this->t('Select taxonomy for template'),
      '#type' => 'select',
      '#options' => $entity_types,
      '#default_value' => $values['term_template'] ?? '',
    ];
    $fields = $this->view->display_handler->getHandlers('field');
    $labels = $this->view->display_handler->getFieldLabels();
    $field_labels = [];
    foreach ($fields as $field_name => $field) {
      $type = $field->options["type"] ?? FALSE;
      if ($type == 'basic_string' || $type == 'email_mailto') {
        $field_labels[$field_name] = $labels[$field_name];
      }
    }
    $element['field_email'] = [
      '#title' => $this->t('Select the email address'),
      '#type' => 'select',
      '#options' => $field_labels,
      '#default_value' => $values['field_email'] ?? '',
      '#description' => empty($field_labels) ? $this->t('You will need field email to send email') : '',
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $vid = $this->context["preconfiguration"]["term_template"];
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid);
    $term_data = [];
    $template = '';
    foreach ($terms as $index => $term) {
      $term_data[$term->tid] = $term->name;
      if (!$index) {
        $template = $term->description__value;
        $subject = $term->name;
      }
    }

    $ajax_wrapper = 'email-template-action';
    $form['term'] = [
      '#title' => $this->t('Select email template'),
      '#type' => 'select',
      '#options' => $term_data,
      '#ajax' => [
        'callback' => [$this, 'getTaxonomyTemplate'],
        'event' => 'change',
        'wrapper' => $ajax_wrapper,
      ],
    ];

    $form['subject'] = [
      '#title' => $this->t('Mail Subject'),
      '#type' => 'textfield',
      '#default_value' => $subject,
    ];

    $form['template'] = [
      '#title' => $this->t('Template'),
      '#type' => 'textarea',
      '#default_value' => $template,
      '#description' => $this->t('The text to display for this field. You may enter data from this view as per the "Replacement patterns" below. You may include <a href="@twig_docs">Twig</a>', [
        '@twig_docs' => 'https://twig.symfony.com/doc/3.x',
      ]),
    ];
    $form['template']['#prefix'] = '<div id="' . $ajax_wrapper . '">';
    $form['template']['#suffix'] = '</div>';
    $args = $form_state->getBuildInfo()["args"];
    $view = \Drupal::entityTypeManager()->getStorage('view')
      ->load($args[0])->getExecutable();
    $view->initDisplay();
    $view->setDisplay($args[1]);
    $previous = $view->display_handler->getFieldLabels();
    $optgroup_arguments = (string) $this->t('Arguments');
    $optgroup_fields = (string) $this->t('Fields');
    foreach ($previous as $id => $label) {
      $options[$optgroup_fields]["{{ $id }}"] = $label;
    }
    foreach ($view->display_handler->getHandlers('argument') as $arg => $handler) {
      $options[$optgroup_arguments]["{{ arguments.$arg }}"] = $this->t('@argument title', ['@argument' => $handler->adminLabel()]);
      $options[$optgroup_arguments]["{{ raw_arguments.$arg }}"] = $this->t('@argument input', ['@argument' => $handler->adminLabel()]);
    }
    $output = [];
    foreach (array_keys($options) as $type) {
      if (!empty($options[$type])) {
        $items = [];
        foreach ($options[$type] as $key => $value) {
          $items[] = $key . ' == ' . $value;
        }
        $item_list = [
          '#theme' => 'item_list',
          '#items' => $items,
        ];
        $output[] = $item_list;
      }
    }
    $form['help'] = [
      '#type' => 'details',
      '#title' => $this->t('Replacement patterns'),
      '#value' => $output,
    ];
    return $form;
  }

  /**
   * The callback function for when the `term` element is changed.
   *
   * What this returns will be replace the wrapper provided.
   */
  public function getTaxonomyTemplate(array $form, FormStateInterface $form_state) {
    // Return the element that will replace the wrapper (we return itself).
    $termSelected = $form_state->getValue('term');
    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($termSelected);
    if (!empty($termSelected)) {
      $form['template']['#value'] = $term->getDescription();
    }
    $form_state->setRebuild(TRUE);
    return $form['template'];
  }

  /**
   * Default configuration form validator.
   *
   * This method will be needed if a child class will implement
   * \Drupal\Core\Plugin\PluginFormInterface. Code saver.
   *
   * @param array &$form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * Default configuration form submit handler.
   *
   * This method will be needed if a child class will implement
   * \Drupal\Core\Plugin\PluginFormInterface. Code saver.
   *
   * @param array &$form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $this->configuration[$key] = $value;
    }
  }

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE)
  {
    // TODO: Implement access() method.
    return $object->access('update', $account, $return_as_object);
  }

  public function execute($entity = NULL)
  {
    if ($entity) {
      $config = $this->getConfiguration();

      $view = $this->view;
      $previous = $view->display_handler->getFieldLabels();
      $context = [];
      foreach ($previous as $fieldName => $field) {
        $entityField = $view->field[$fieldName];
        $key = array_search($entity->id(), array_column($view->result, 'nid'));
        $context[$fieldName] = $entityField->advancedRender($view->result[$key]);
      }

      $billing_profile = $entity->getBillingProfile();
      $context['email_to'] = $billing_profile->get($config['field_email'])->getValue()[0]["value"];

      $context['name_to']  = $entity->label();

      if($context['email_to']) {
        $template = getTemplateTerm($entity, $config['term'], $context, $config);
        $mailManager = \Drupal::service('plugin.manager.mail');
        $mailManager->getInstance([
          'module' => 'pizzahips',
        ])->mail($template);
      }
    }
  }
}

function getTemplateTerm($entity , $term_id, $context, $config, $langcode = '') {
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['tid' => $term_id]);
  $term = reset($term);
  if (!empty($term)) {
    $site_name = \Drupal::config('system.site')->get('name');

    $subject = $config['subject'];
    $rendererBody = [
      '#type' => 'inline_template',
      '#template' => $config['template'],
      '#context' => $context
    ];
    $body = \Drupal::service('renderer')->render($rendererBody);
    $site_email = \Drupal::config('system.site')->get('mail');
    return [
      'headers' => [
        'Content-type' => 'text/html; charset=UTF-8; format=flowed; delsp=yes',
        'Reply-to' => $site_name . '<' . $site_email . '>',
        'Content-Transfer-Encoding' => '8Bit',
        'MIME-Version' => '1.0',
      ],
      'to' => $context['name_to'] . '<' . $context['email_to'] . '>',
      'subject' => $subject,
      'body' => $body,
    ];
  }
  return [];
}
