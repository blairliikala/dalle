<?php

namespace Blairliikala\Dalle\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;

class Test extends AbstractRoute
{
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

  }

}