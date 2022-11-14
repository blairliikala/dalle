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
    if (empty($this->settings))
    {
        $this->settings = ee('dalle:settings')::get();
    }
    ee('dalle:utilities')->generateSidebar();
  }

  public function process($id = false)
  {

    // Waiting for EE to change some things before moving mcp methods to this location.

    $this->addBreadcrumb('settings', lang('settings'));
    $this->setHeading(lang('settings'));

    // Important stuff goes here.

    $this->setBody('cp/formbug', $vars);
    return $this;

  }

}