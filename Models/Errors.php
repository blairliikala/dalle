<?php

namespace Blairliikala\Dalle\Models;

use ExpressionEngine\Service\Model\Model;

class Errors extends Model {

    protected static $_primary_key = 'id';
    protected static $_table_name = 'dalle_errors';

    protected $id;
    protected $site_id = 1;
    protected $code;
    protected $message;
    protected $status;
    protected $created;

    protected static $_typed_columns = array(
      'id' => 'int',
      'site_id' => 'int',
      'code' => 'string',
      'message' => 'string',
      'status' => 'string',
      'created' => 'int',
    );
}