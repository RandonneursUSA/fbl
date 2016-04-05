<?php

/**
 * @file
 * Contains \Drupal\fbl\Form\FblConfiguration.
 */

namespace Drupal\fbl\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

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

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['fbl.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['field_based_login'] = [
      '#type' => 'fieldset',
      '#title' => t('Field based login Configurations'),
      //'#description' => t('abcd..'),
    '#collapsible' => FALSE,
      '#tree' => TRUE,
    ];
    $field_based_login = variable_get('field_based_login', []);

    // todo : in feature profile 2 should work :)
    $user_fields = field_info_instances('user', 'user');
    $fields = [];
    foreach ($user_fields as $field => $values) {
      $field_type = field_info_field($field)['type'];
      if (($field_type == 'number_integer' || $field_type == 'text')) {
        $fields[$field] = $values['label'];
      }
    }

    $form['field_based_login']['field'] = [
      '#type' => 'select',
      '#title' => t('Unique field'),
      '#options' => $fields,
      '#empty_option' => '- Select -',
      '#default_value' => isset($field_based_login['field']) ? $field_based_login['field'] : '',
      '#description' => t('Unique field to allow users to login with this field. Note : Selected field will become unique filed.'),
    ];

    $form['field_based_login']['allow_user_name'] = [
      '#type' => 'checkbox',
      '#title' => check_plain(t('Allow login with username')),
      '#default_value' => isset($field_based_login['allow_user_name']) ? $field_based_login['allow_user_name'] : 1,
    ];

    $form['field_based_login']['allow_user_email'] = [
      '#type' => 'checkbox',
      '#title' => check_plain(t('Allow login with E-mail')),
      '#default_value' => isset($field_based_login['allow_user_email']) ? $field_based_login['allow_user_email'] : '',
    ];

    $form['field_based_login']['label'] = [
      '#type' => 'textfield',
      '#title' => t('User login form - User name field Label'),
      '#default_value' => isset($field_based_login['label']) ? $field_based_login['label'] : '',
      '#description' => t('Ex: Phone / E-mail'),
      '#size' => 60,
      '#maxlength' => 60,
    ];

    /*added description field for configuration */
    $form['field_based_login']['field_desc'] = [
      '#type' => 'textfield',
      '#title' => t('User login form - User name field Description'),
      '#default_value' => isset($field_based_login['field_desc']) ? $field_based_login['field_desc'] : '',
      '#description' => t('Ex: Provide description for custom login field'),
      '#size' => 60,
      '#maxlength' => 60,
    ];

    $form['#validate'][] = '_fbl_configuration_validate';

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // todo: validate selected field must be unique
  // todo : field_validator module support
    $field_name = $form_state->getValue(['field_based_login', 'field']);
    $field_type = field_info_instance('user', $field_name, 'user');
    $user_count = _fbl_user_count();
    $field_data_count = _fbl_field_data_count($field_type['field_name']);
    if ($field_type['required'] == FALSE) {
      drupal_set_message(t('Selected field is not mandatory.'), 'warning');
    }
    elseif ($field_type['settings']['user_register_form'] == FALSE) {
      drupal_set_message(t('Selected field is not visible to user in the Registration form.'), 'warning');
    }
    if ($user_count > $field_data_count) {
      drupal_set_message(t('Selected field is not having data for some of the user'), 'warning');
    }
    $field = $form_state->getValue(['field_based_login', 'field']);
    if ($field) {
      if (_fbl_check_for_duplicates($field)) {
        $form_state->setErrorByName('field_based_login', t('Selected field is not unique filed. There are duplicates found, Please contact administration or change different field.'));
      }
    }
  }

}
