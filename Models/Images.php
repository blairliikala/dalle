<?php

namespace Blairliikala\Dalle\Models;

use ExpressionEngine\Service\Model\Model;

class Images extends Model {

    protected static $_primary_key = 'id';
    protected static $_table_name = 'dalle_images';

    protected $id;
    protected $site_id = 1;
    protected $created;
    protected $phrase;
    protected $file_id;
    protected $base64;

    protected static $_typed_columns = array(
      'id' => 'int',
      'site_id' => 'int',
      'created' => 'string', // timestamp
      'phrase' => 'string',
      'file_id' => 'int',
      'base64' => 'string',
    );

    /*
    protected static $_relationships = array(
      'File' => array(
          'type'      => 'belongsTo',
          'from_key'  => 'file_id',
          'to_key'    => 'file_id'
      ),
    );
    */

}