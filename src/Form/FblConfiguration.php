<?php

/**
 * @file
 * Contains \Drupal\fbl\Form\FblConfiguration.
 */

namespace Drupal\fbl\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\field\Entity\FieldStorageConfig;

class FblConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_fbl_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['fbl.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    //Getting the configuration value
    $default_value_config = $this->config('fbl.settings');
    $default_value = $default_value_config->get('field_based_login');
    $form['field_based_login'] = [
      '#type' => 'fieldset',
      '#title' => t('Field based login Configurations'),
      '#collapsible' => FALSE,
      '#tree' => TRUE,
    ];
    // todo : in feature profile 2 should work :
    $entity_type_id = 'user';
    $bundle = 'user';
    $bundleFields = array();
    foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        if ($field_definition->getType() == 'string' || $field_definition->getType() == 'integer') {
          $bundleFields[$field_name] = $field_definition->getLabel();
        }
      }
    }
    $form['field_based_login']['field'] = [
      '#type' => 'select',
      '#title' => t('Unique field'),
      '#options' => $bundleFields,
      '#empty_option' => '- Select -',
      '#default_value' => isset($default_value['field']) ? $default_value['field'] : '',
      '#description' => t('Unique field to allow users to login with this field. Note : Selected field will become unique filed.'),
    ];

    $form['field_based_login']['allow_user_name'] = [
      '#type' => 'checkbox',
      '#title' => t('Allow login with username'),
      '#default_value' => isset($default_value['allow_user_name']) ? $default_value['allow_user_name'] : 1,
    ];

    $form['field_based_login']['allow_user_email'] = [
      '#type' => 'checkbox',
      '#title' => t('Allow login with E-mail'),
      '#default_value' => isset($default_value['allow_user_email']) ? $default_value['allow_user_email'] : '',
    ];

    $form['field_based_login']['label'] = [
      '#type' => 'textfield',
      '#title' => t('User login form - User name field Label'),
      '#default_value' => isset($default_value['label']) ? $default_value['label'] : '',
      '#description' => t('Ex: Phone / E-mail'),
      '#size' => 60,
      '#maxlength' => 60,
    ];

    /* added description field for configuration */
    $form['field_based_login']['field_desc'] = [
      '#type' => 'textfield',
      '#title' => t('User login form - User name field Description'),
      '#default_value' => isset($default_value['field_desc']) ? $default_value['field_desc'] : '',
      '#description' => t('Ex: Provide description for custom login field'),
      '#size' => 60,
      '#maxlength' => 60,
    ];

    $form['#validate'][] = '_fbl_configuration_validate';

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $field_name = $form_state->getValue(['field_based_login', 'field']);
    $user_count = _fbl_user_count();
    $field_data_count = _fbl_field_data_count($field_name);
    $entity_type_id = 'user';
    $bundle = 'user';
    foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name_value => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        if ($field_name_value == $field_name) {
          $is_required = $field_definition->isRequired();
        }
      }
    }
    if ($is_required == '') {
      drupal_set_message(t('Selected field is not mandatory.'), 'warning');
    }
    if ($user_count > $field_data_count) {
      drupal_set_message(t('Selected field is not having data for some of the user'), 'warning');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('fbl.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

}

/**
 * Returns number of user
 * @return string
 */
function _fbl_user_count() {
  $query = db_select('users', 'u');
  $query->fields('u', array('uid'));
  $query->condition('u.uid', '0', '!=');
  $user_count = $query->countQuery()->execute()->fetchField();
  return $user_count;
}

/**
 * @param string $field_name
 * @return integer
 * Returns how many fields having value
 */
function _fbl_field_data_count($field_name) {
  $table_name = 'user__' . $field_name;
  $table_column = $field_name . '_value';
  $query = db_select($table_name, 'field_value');
  $query->fields('field_value', array($table_column));
  $query->condition('field_value.bundle', 'user');
  $field_data_count = $query->countQuery()->execute()->fetchField();
  return $field_data_count;
}
