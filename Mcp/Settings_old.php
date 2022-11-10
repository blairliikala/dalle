<?php

namespace Blairliikala\Dalle\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;

class Settings extends AbstractRoute
{
  protected $route_path = 'settings';
  protected $cp_page_title = 'Settings';
  protected $settings = array();

  function __construct()
  {
    ee('dalle:utilities')->generateSidebar();

    if (empty($this->settings))
    {
        $this->settings = ee('dalle:settings')::get();
    }
  }

  public function process($id = false)
  {
    $this->addBreadcrumb('settings', lang('settings'));
    $this->setHeading(lang('settings'));

    /*
    $form = ee('CP/Form');
    $form->setCpPageTitle(lang('Settings'));
    $form->setBaseUrl(ee('CP/URL', 'addons/settings/dalle/_update_settings'));

    $field_group = $form->getGroup('Settings');

    $field_set = $field_group->getFieldSet(lang('token'));
    $field_set->getField('token', 'text')->setValue($this->settings['token'] ?? '');
    */

    $upload_destinations = ee('Model')->get('UploadDestination')
        ->filter('site_id', ee()->config->item('site_id'))
        ->filter('module_id', 0)
        ->order('name', 'asc');

    $destinations = array();
    foreach ($upload_destinations->all() as $destination) {
      if ($destination->memberHasAccess(ee()->session->getMember()) === false) {
          continue;
      }
      $display_name = htmlspecialchars($destination->name, ENT_QUOTES, 'UTF-8');
      $destinations[$destination->getId()] = $display_name;
    }

    /*
    $field_group->getFieldSet(lang('upload_directory'))
      ->getField('Upload Directory', 'radio')
      //->setTitle('Select directory to upload created images to.')
      ->setValue($this->settings['destination_id'] ?? '');
    $form->toArray();
    */

    /*
    $vars['sections'] = array(
      array(
        array(
          'title' => lang('token'),
          'desc' => lang('token_desc'),
          'fields' => array(
            'token' => array(
              'type' => 'text',
              'value' => $this->settings['token'] ?? '',
            )
          )
        ),
        array(
          'title' => lang('default_size'),
          'desc' => lang('default_size_desc'),
          'fields' => array(
            'size' => array(
              'type' => 'radio',
              'value' => isset($this->settings['size']) ? $this->settings['size'] : '256x256',
              'choices' => [
                '256x256' => '256x256' ,
                '512x512' => '512x512',
                '1024x1024' => '1024x1024',
              ]
            )
          )
        ),        
        array(
          'title' => lang('upload_directory'),
          'desc' => lang('upload_directory_desc'),
          'fields' => array(
            'destination_id' => array(
              'type' => 'radio',
              'value' => $this->settings['destination_id'] ?? NULL,
              'choices' => $destinations,
            )
          )
        )
      )
    );
    */

    $vars += array(
      'base_url' => ee('CP/URL', 'addons/settings/dalle/_update_settings'),
      'cp_page_title' => lang('settings'),
      'save_btn_text' => 'btn_save_settings',
      'save_btn_text_working' => 'btn_saving'
    );

    $this->setBody('cp/formbug', $vars);
    return $this;

  }

}