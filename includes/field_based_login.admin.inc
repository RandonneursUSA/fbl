<?php

function _field_based_login_configuration($form, &$form_state){
  $form['field_based_login'] = array(
    '#type' => 'fieldset',
    '#title' => t('Field based login fieldset'),
    '#description' => t('abcd..'),
    '#collapsible' => FALSE,
    '#tree' => TRUE,
  );
  $field_based_login = variable_get('field_based_login', array());
  $form['field_based_login']['field_based_test'] = array(
    '#type' => 'textfield',
    '#title' => t('field1'),
    '#description' => t('Fields .s...'),
    '#default_value' => isset($field_based_login['field_based_test']) ? $field_based_login['field_based_test'] : '',
  );
  return system_settings_form($form);
}