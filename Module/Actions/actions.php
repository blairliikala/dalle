<?php

namespace Blairliikala\Dalle\Module\Actions;

use ExpressionEngine\Service\Addon\Controllers\Action\AbstractRoute;

class Actions extends AbstractRoute
{
    public function process()
    {
        $method = $_POST['method'];

        if (!$method)
        {
          $this->output(ee('dalle:utilities')->error('POST request does not have a method.'));
        }

        $post = json_decode(file_get_contents('php://input'), TRUE);
        
        if (!$post)
        {
          $this->output(ee('dalle:utilities')->error('POST request not formatted for JSON.'));
        }

        switch(true)
        {
          case $method === 'create' : 

            $phrase = $post['phrase'] ?? NULL;
            $size   = $post['size'] ?? '';
            $cache  = $post['cache'] ?? TRUE;

            // Phrase is required by API.
            if (empty($phrase))
            {
              $this->output(ee('dalle:utilities')->error('No phrase included.'));
            }

            list($file) = ee('dalle:images')->get($phrase, $size, $cache);
            $this->output($file);
            break;

          case $method === 'get' :
            break;

          default:
                
        }
    }

    private function output($output)
    {
      header('Content-Type: application/json');
      echo json_encode($output);
      exit;
    }
}
