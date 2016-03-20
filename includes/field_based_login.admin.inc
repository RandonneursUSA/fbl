<?php

function _field_based_login_configuration($form, &$form_state){
  $form['field_based_login'] = array(
    '#type' => 'fieldset',
    '#title' => t('Field based login Configurations'),
    //'#description' => t('abcd..'),
    '#collapsible' => FALSE,
    '#tree' => TRUE,
  );
  $field_based_login = variable_get('field_based_login', array());

  // todo : in feature profile 2 should work :)
  $user_fields = field_info_instances('user','user');
  $fields = array();
  foreach($user_fields as $field=>$values){
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
    '#default_value' => isset($field_based_login['allow_user_name']) ? $field_based_login['allow_user_name'] : '',
  );

  $form['field_based_login']['allow_user_email'] = array(
    '#type' => 'checkbox',
    '#title' => check_plain(t('Allow login with E-mail')),
    '#default_value' => isset($field_based_login['allow_user_email']) ? $field_based_login['allow_user_email'] : '',
  );
  $form['#validate'][] = '_field_based_login_configuration_validate';

  return system_settings_form($form);
}

function _field_based_login_configuration_validate($form, &$form_state){
  // todo: validate selected field must be unique
  if(_field_based_check_for_duplicates()){
    form_set_error('field_based_login[field_based_test]', t('Selected field is not unique filed. There are duplicates found, Please contact administration or change different field.'));
  }
}

function _field_based_check_for_duplicates(){

  //SELECT field_phone_value, COUNT(*) c FROM field_data_field_phone GROUP BY field_phone_value HAVING c > 1;
  $duplicate_count = db_query("SELECT {field_phone_value}, COUNT(*) c FROM field_data_field_phone GROUP BY field_phone_value HAVING c > 1 limit 0,1")->fetchObject();
  if($duplicate_count){
    return 0;
  }
  return false;
}