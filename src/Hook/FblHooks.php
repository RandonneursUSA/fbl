<?php

namespace Drupal\fbl\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
/**
 * Hook implementations for fbl.
 */
class FblHooks
{
    use StringTranslationTrait;
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
        // Main module help for the content_type_dependency module.
        if ($route_name == 'help.page.fbl') {
            $output = '<h3>' . $this->t('About') . '</h3>';
            $output .= '<p>' . $this->t('Field Based login') . '</p>';
            $output .= '<p>' . $this->t('This module extends drupal core user login feature, using this module admin is allows to configure user account field as loggable. like mobile number or serial number field & users can login with the same mobile number or serial number field. for more info visit <a href=":link">here</a>..', [
                ':link' => 'https://www.drupal.org/project/fbl',
            ]) . '</p>';
            $output .= '<h3>' . $this->t('Uses') . '</h3>';
            $output .= '<dl>';
            $output .= '<dt>' . $this->t('Assigning user field to user login') . '</dt>';
            return $output;
        }
    }
    /**
     * Implements hook_form_alter().
     */
    #[Hook('form_alter')]
    public function formAlter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id)
    {
        // Alter user login form.
        if ($form_id == 'user_login_form') {
            // Load fbl configurations.
            $fbl_config = \Drupal::config('fbl.settings');
            $fbl_value = $fbl_config->get('field_based_login');
            // Add validation.
            array_unshift($form['#validate'], 'fbl_login_name_validate');
            // Alter name title.
            if (!empty($fbl_value['label'])) {
                $form['name']['#title'] = \Drupal\Component\Utility\Html::escape($fbl_value['label']);
            }
            // Alter name field description.
            if (!empty($fbl_value['field_desc'])) {
                $form['name']['#description'] = \Drupal\Component\Utility\Html::escape($fbl_value['field_desc']);
            }
        }
        // Set fbl validator on top of other register validators.
        if ($form_id == 'user_form' || $form_id == 'user_register_form') {
            $form['#validate'][] = 'fbl_user_register_validate';
        }
    }
}
