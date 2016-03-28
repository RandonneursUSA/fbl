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
    $fields[$field] = $values['label'];
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

  $form['#validate'][] = '_fbl_configuration_validate';

  return system_settings_form($form);
}

function _fbl_configuration_validate($form, &$form_state) {
  // todo: validate selected field must be unique
  // todo : field_validator module support
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
  $duplicate_count = $query->countQuery()->execute()->fetchField();
  // todo : exception handling
  if ($duplicate_count > 1) {
    return TRUE;
  }
  return FALSE;
}
