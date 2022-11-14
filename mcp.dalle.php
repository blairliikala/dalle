<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Mcp;

class Dalle_mcp extends Mcp
{
    protected $addon_name = 'dalle';
    protected $settings = array();

    function __construct()
    {
      if (empty($this->settings))
      {
          $this->settings = ee('dalle:settings')::get();
      }
      ee('dalle:utilities')->generateSidebar();
    }

    public function index()
    {
      $html = '';

      if (!isset($this->settings['token']) OR empty($this->settings['token']))
      {
        $html .= ee('CP/Alert')->makeInline()
          ->asWarning()
          ->withTitle( lang('missing_access_token') )
          ->addToBody( lang('missing_access_token_desc') )
          ->render();
      } 

      $vars = array(
        'heading' => lang('generate'),
        'size' => $this->settings['size'],
        'base_path' => ee('CP/URL')->make('addons/settings/dalle/api'),
        'base_file' => ee('CP/URL')->make('files/file/view'),
      );
      $html .= ee('View')->make('dalle:cp/generate')->render($vars);

      return array(
        'body'    => $html,
        'heading' => lang('generate'),
        'breadcrumb' => array(
            ee('CP/URL')->make('addons/settings/dalle/generate')->compile() => lang('generate'),
        ),
      );      

    }


    public function list()
    {

      $vars['heading'] = lang('images');

      $images = ee('Model')->get('dalle:Images')->order('created', 'DESC')->all();
      $total_results = $images->count();

      $filters = ee('CP/Filter');
      $filters->add('Perpage', $total_results);
      $vars['filters'] = $filters->render(ee('CP/URL', 'addons/settings/dalle/list'));

      $filter_values = $filters->values();

      $page       = ((int) ee('Request')->get('page')) ?: 1;
      $offset     = (int) ($page - 1) * $filter_values['perpage'];

      $images1 = ee('Model')
                      ->get('dalle:Images')
                      ->offset($offset)
                      ->limit($filter_values['perpage'])
                      ->all();

      $table = ee('CP/Table', array(
          //'autosort' => TRUE,
          //'autosearch' => TRUE,
          )
      );
      $table->setColumns(
          array(
              'ID',
              'Image' => array(
                'encode' => false
              ),
              'Phrase',
              'Created',
              'Asset',
          )
      );

      $table_data = array();

      foreach($images as $row)
      {
          $time_format  = ee()->localize->human_time($row->created);
          $asset_link   = ee('CP/URL', 'files/file/view/'.$row->file_id);
          $asset = ee('Model')->get('File', $row->file_id)->first();
          if (!$asset)
          {
            continue;
          }
          $image_url = $asset ? $asset->getAbsoluteURL() : ''; // Should be temp?

          $table_data[] = array(
              $row->id,
              '<img src="'.$image_url.'" style="width:75px" />',
              $row->phrase,
              $time_format,
              array(
                'content' => 'File',
                'href' => $asset_link,
              )
          );
      }
      $table->setData($table_data);
      $table->setNoResultsText(lang('no_results'), 'Refresh', ee('CP/URL', 'addons/settings/dalle/list')->addQueryStringVariables($filter_values));
      $vars['table'] = ee('View')->make('_shared/table')->render($table->viewData(ee('CP/URL', 'addons/settings/dalle/list')));

      $vars['pagination'] = ee('CP/Pagination', $total_results)
              ->perPage($filter_values['perpage'])
              ->currentPage($page)
              ->render(ee('CP/URL', 'addons/settings/dalle/list')->addQueryStringVariables($filter_values));


      $html = '';

      if (!isset($this->settings['token']) OR empty($this->settings['token']))
      {
        $html .= ee('CP/Alert')->makeInline('no')
          ->asWarning()
          ->withTitle( lang('missing_access_token') )
          ->addToBody( lang('missing_access_token_desc') )
          ->render();
      }

      $html .= ee('View')->make('dalle:cp/images')->render($vars);

      return array(
          'body'    => $html,
          'heading' => lang('Images'),
          'breadcrumb' => array(
              ee('CP/URL')->make('addons/settings/dalle/list')->addQueryStringVariables($filter_values)->compile() => lang('Images'),
          ),
      );

    }

  
    public function settings()
    {

      if (! ee('Permission')->hasAll('can_access_addons', 'can_admin_addons')) {
        return ee('CP/Alert')->makeInline('no_access')
            ->asIssue()
            ->withTitle(lang('unauthorized_access'))
            ->addToBody(lang('unauthorized_access_settings'))
            ->render();
      }

      $upload_destinations = ee('Model')->get('UploadDestination')
          ->filter('site_id', ee()->config->item('site_id'))
          ->filter('module_id', 0)
          ->order('name', 'asc');

      $destinations = array();
      foreach ($upload_destinations->all() as $destination) {
        if ($destination->memberHasAccess(ee()->session->getMember()) === false) {
          continue;
        }
        // Skip everything but local until we can figure out how to get other adaptors to work (S3..etc)
        if ($destination->adapter !== 'local') {
          continue;
        }
        $display_name = htmlspecialchars($destination->name, ENT_QUOTES, 'UTF-8');
        $destinations[$destination->getId()] = $display_name;
      }

      $vars['sections'] = array(
        array(
          array(
            'title' => lang('token'),
            'desc' => lang('token_desc'),
            'fields' => array(
              'token' => array(
                'type' => 'text',
                'value' => isset($this->settings['token']) ? $this->settings['token'] : '',
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
                'value' => $this->settings['destination_id'] ?? '',
                'choices' => $destinations,
              )
            )
          )

        )
      );

      $vars += array(
        'base_url' => ee('CP/URL', 'addons/settings/dalle/_update_settings'),
        'cp_page_title' => lang('settings'),
        'save_btn_text' => 'btn_save_settings',
        'save_btn_text_working' => 'btn_saving'
      );

      $html = '';

      if (!isset($this->settings['token']) OR empty($this->settings['token']))
      {
        $html .= ee('CP/Alert')->makeInline()
          ->asWarning()
          ->withTitle( lang('missing_access_token') )
          ->addToBody( lang('missing_access_token_desc') )
          ->render();
      }      

      $html .= ee('View')->make('ee:_shared/form')->render($vars);      

      return [
        'body' => $html,
        'breadcrumb' => [
            ee('CP/URL')->make('addons/settings/dalle/settings')->compile() => lang('settings')
        ],
        'heading' => lang('settings'),
      ];

    }


    public function _update_settings()
    {
      $result = ee('dalle:settings')->save($_POST);
      $url = ee('CP/URL')->make('addons/settings/dalle/settings');

      if ($result[0] === TRUE)
      {
        ee('CP/Alert')->makeBanner('Success')->addToBody('Success')->asSuccess()->defer();
        ee()->functions->redirect($url);
      }
      else
      {
        ee('CP/Alert')->makeBanner('Fail')->addToBody('Fail')->asIssue()->defer();
        return ee()->output->show_user_error('submission', $result[1], '', $url);
      }
    }


    public function errorlog()
    {

      $vars['heading'] = lang('error_log');

      $images = ee('Model')->get('dalle:Errors')->order('created', 'DESC')->all();
      $total_results = $images->count();

      $filters = ee('CP/Filter');
      $filters->add('Perpage', $total_results);
      $vars['filters'] = $filters->render(ee('CP/URL', 'addons/settings/dalle/errorlog'));

      $filter_values = $filters->values();

      $page       = ((int) ee('Request')->get('page')) ?: 1;
      $offset     = (int) ($page - 1) * $filter_values['perpage'];

      $table = ee('CP/Table', array());
      $table->setColumns(
          array(
              'ID',
              'Created',
              'Status',
              'Message',
          )
      );

      $table_data = array();

      foreach($images as $row)
      {
          $time_format  = ee()->localize->human_time($row->created);
          $table_data[] = array(
              $row->id,
              $time_format,
              $row->status,
              $row->message,
          );
      }
      $table->setData($table_data);
      $table->setNoResultsText(lang('no_results'), 'Refresh', ee('CP/URL', 'addons/settings/dalle/errorlog')->addQueryStringVariables($filter_values));
      $vars['table'] = ee('View')->make('_shared/table')->render($table->viewData(ee('CP/URL', 'addons/settings/dalle/errorlog')));

      $vars['pagination'] = ee('CP/Pagination', $total_results)
              ->perPage($filter_values['perpage'])
              ->currentPage($page)
              ->render(ee('CP/URL', 'addons/settings/dalle/errorlog')->addQueryStringVariables($filter_values));

      return array(
          'body'    => ee('View')->make('dalle:cp/errorlog')->render($vars),
          'heading' => lang('error_log'),
          'breadcrumb' => array(
              ee('CP/URL')->make('addons/settings/dalle/errorlog')->compile() => lang('error_log'),
          ),
      );
    }


    public function api()
    {

      $method = ee()->input->get('method');

      if ( ! $method)
      {
          header('Content-Type: application/json');
          echo json_encode(ee('dalle:utilities')->error('No request method. Add method= to url query.'));
          exit;
      }

      switch(true)
      {
        case $method === 'get' :
          $phrase = ee()->input->get('phrase') ? urldecode(ee()->input->get('phrase')) : '';
          $size   = ee()->input->get('size') ? ee()->input->get('size') : '';
          $cache  = ee()->input->get('cache') ? ee()->input->get('cache') : TRUE;
          $images = ee('dalle:images')->get($phrase, $size, $cache);

          $result = array();
          foreach($images as $image)
          {
            if ( ! $image)
            {
              continue;
            }        

            if (isseT($image->error))
            {
              $result = $images;
              continue;
            }

            $result[] = array(
              'url' => $image->getAbsoluteURL(),
              'thumbnail_url' => $image->getAbsoluteThumbnailURL(),
              'file_id' => $image->file_id ,
              'title' => $image->title,
              'site_id' => $image->site_id,
              'upload_location_id' => $image->upload_location_id,
              'directory_id' => $image->directory_id,
              'mime_type' => $image->mime_type,
              'file_type' => $image->file_type,
              'file_name' => $image->file_name,
              'file_size' => $image->file_size,
              'description' => $image->description,
              'credit' => $image->credit,
              'location' => $image->location,
              'uploaded_by_member_id' => $image->uploaded_by_member_id,
              'upload_date' => $image->upload_date,
              'modified_by_member_id' => $image->modified_by_member_id,
              'modified_date' => $image->modified_date,
              'file_hw_original' => $image->file_hw_original,
            );
          }
          break;
        
        default:
          $result = ee('dalle:utilities')->error('No matching method.');
      }

      ee('dalle:utilities')->outputJSON($result);

    }

}
