<?php

namespace Blairliikala\Dalle\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;

class Index extends AbstractRoute
{
    protected $route_path = 'index';
    protected $cp_page_title = 'home';
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
        $this->addBreadcrumb('index', lang('images'));

        $vars['heading'] = lang('images');

        $images = ee('Model')->get('dalle:Images')->all();
        $total_results = $images->count();

        $filters = ee('CP/Filter');
        $filters->add('Perpage', $total_results);
        $vars['filters'] = $filters->render(ee('CP/URL', 'addons/settings/dalle'));

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
                'test'
            )
        );

        $table_data = array();

        foreach($images as $row)
        {
            $time_format  = ee()->localize->human_time($row->created);
            $asset_link   = ee('CP/URL', 'files/file/view/'.$row->file_id);
            $asset = ee('Model')->get('File', $row->file_id)->first();

            $test = $asset->

            $table_data[] = array(
                $row->id,
                '<img src="data:image/png;base64,'.$row->base64.'" style="width:75px" />',
                $row->phrase,
                $time_format,
                array(
                    'content' => 'File',
                    'href' => $asset_link,
                ),
                $test,
            );
        }
        $table->setData($table_data);
        $table->setNoResultsText(lang('no_results'), 'Refresh', ee('CP/URL', 'addons/settings/dalle/')->addQueryStringVariables($filter_values));
        $vars['table'] = ee('View')->make('_shared/table')->render($table->viewData(ee('CP/URL', 'addons/settings/dalle/')));

        $vars['pagination'] = ee('CP/Pagination', $total_results)
                ->perPage($filter_values['perpage'])
                ->currentPage($page)
                ->render(ee('CP/URL', 'addons/settings/dalle/')->addQueryStringVariables($filter_values));


        if (!$this->settings['token'] OR empty($this->settings['token']))
        {

        }

        $this->setBody('cp/images', $vars);
        return $this;

        /*
        return array(
            'body'    => ee('View')->make('dalle:cp/images')->render($vars),
            'heading' => lang('Images'),
            'breadcrumb' => array(
                ee('CP/URL')->make('addons/settings/dalle/logs')->addQueryStringVariables($filter_values)->compile() => lang('Images'),
            ),
        );
        */

    }

}
