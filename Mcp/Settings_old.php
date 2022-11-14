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
    $this->addBreadcrumb('settings', lang('settings'));
    $this->setHeading(lang('settings'));

    $this->setBody('cp/formbug', $vars);
    return $this;

  }

}