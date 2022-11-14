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
      if (empty($this->settings))
      {
          $this->settings = ee('dalle:settings')::get();
      }
      ee('dalle:utilities')->generateSidebar();
    } 

    public function process($id = false)
    {

    }

}
