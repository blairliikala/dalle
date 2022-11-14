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
  
      // Waiting for EE to change some things before moving mcp methods to this location.
  
      $this->addBreadcrumb('generate', lang('generate'));
      $this->setHeading(lang('generate'));
  
      // Important stuff goes here.
  
      $this->setBody('cp/formbug', $vars);
      return $this;
  
    }

}
