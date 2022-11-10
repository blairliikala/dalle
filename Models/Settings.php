<?php

namespace Blairliikala\Dalle\Models;

use ExpressionEngine\Service\Model\Model;

class Settings extends Model {

    protected static $_primary_key = 'id';
    protected static $_table_name = 'dalle_settings';

    protected $id;
    protected $site_id = 1;
    protected $name;
    protected $value;

    protected static $_typed_columns = array(
      'id' => 'int',
      'site_id' => 'int',
      'name' => 'string',
      'value' => 'string',
    );

}