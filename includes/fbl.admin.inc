<?php

function _fbl_configuration($form, &$form_state) {
  $form['field_based_login'] = array(
    '#type' => 'fieldset',
    '#title' => t('Field based login Configurations'),
    //'#description' => t('abcd..'),
    '#collapsible' => FALSE,
    '#tree' => TRUE,
  );
  $field_based_login = variable_get('field_based_login', array());

  // todo : in feature profile 2 should work :)
  $user_fields = field_info_instances('user', 'user');
  $fields = array();
  foreach ($user_fields as $field => $values) {
    $field_type = field_info_field($field)['type'];
    if (($field_type == 'number_integer' || $field_type == 'text')) {
      $fields[$field] = $values['label'];
    }
  }

  $form['field_based_login']['field'] = array(
    '#type' => 'select',
    '#title' => t('Unique field'),
    '#options' => $fields,
    '#empty_option' => '- Select -',
    '#default_value' => isset($field_based_login['field']) ? $field_based_login['field'] : '',
    '#description' => t('Unique field to allow users to login with this field. Note : Selected field will become unique filed.'),
  );

  $form['field_based_login']['allow_user_name'] = array(
    '#type' => 'checkbox',
    '#title' => check_plain(t('Allow login with username')),
    '#default_value' => isset($field_based_login['allow_user_name']) ? $field_based_login['allow_user_name'] : 1,
  );

  $form['field_based_login']['allow_user_email'] = array(
    '#type' => 'checkbox',
    '#title' => check_plain(t('Allow login with E-mail')),
    '#default_value' => isset($field_based_login['allow_user_email']) ? $field_based_login['allow_user_email'] : '',
  );

  $form['field_based_login']['label'] = array(
    '#type' => 'textfield',
    '#title' => t('User login form - User name field Label'),
    '#default_value' => isset($field_based_login['label']) ? $field_based_login['label'] : '',
    '#description' => t('Ex: Phone / E-mail'),
    '#size' => 60,
    '#maxlength' => 60,
  );

  /*added description field for configuration */
  $form['field_based_login']['field_desc'] = array(
    '#type' => 'textfield',
    '#title' => t('User login form - User name field Description'),
    '#default_value' => isset($field_based_login['field_desc']) ? $field_based_login['field_desc'] : '',
    '#description' => t('Ex: Provide description for custom login field'),
    '#size' => 60,
    '#maxlength' => 60,
  );

  $form['#validate'][] = '_fbl_configuration_validate';

  return system_settings_form($form);
}

function _fbl_configuration_validate($form, &$form_state) {
  // todo: validate selected field must be unique
  // todo : field_validator module support
  $field_name = $form_state['values']['field_based_login']['field'];
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
  $field = $form_state['values']['field_based_login']['field'];
  if ($field) {
    if (_fbl_check_for_duplicates($field)) {
      form_set_error('field_based_login', t('Selected field is not unique filed. There are duplicates found, Please contact administration or change different field.'));
    }
  }
}

function _fbl_check_for_duplicates($field) {

  $field = field_info_field($field);
  $storage_details = $field['storage']['details']['sql']['FIELD_LOAD_CURRENT'];
  foreach ($storage_details as $table => $column) {
    $table_name = $table;
    $table_column = $column['value'];
  }
  //query to find the number of unique values of the login selected field
  $query = db_select($table_name, 't');
  $query->fields('t', array($table_column));
  $query->groupBy('t.' . $table_column);
  $query->condition('t.entity_type', 'user');
  $query->condition('t.bundle', 'user');
  $query->addExpression('COUNT(' . $table_column . ')', 'field_count');
  $query->havingCondition('field_count', '1', '>');
  $query->range(0, 1);
  $duplicate_count = $query->execute()->fetchAll();
  // todo : exception handling
  if ($duplicate_count) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Returns number of users reigstered in the site
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
  $field = field_info_field($field_name);
  $storage_details = $field['storage']['details']['sql']['FIELD_LOAD_CURRENT'];
  foreach ($storage_details as $table => $column) {
    $table_name = $table;
    $table_column = $column['value'];
  }
  $query = db_select($table_name, 'field_value');
  $query->fields('field_value', array($table_column));
  $query->condition('field_value.entity_type', 'user');
  $query->condition('field_value.bundle', 'user');
  $field_data_count = $query->countQuery()->execute()->fetchField();
  return $field_data_count;
}
