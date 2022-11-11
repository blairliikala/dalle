<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Installer;

class Dalle_upd extends Installer
{
    public $has_cp_backend = 'y';
    public $has_publish_fields = 'n';

    public $actions = [
        [
            'class' => 'dalle',
            'method' => 'actions'
        ]
    ];

    public function __construct()
    {
      parent::__construct();
      ee()->load->dbforge();
      ee()->load->library('smartforge');
    }

    public function install()
    {
        parent::install();

        if( ! ee()->db->table_exists('dalle_settings') )
        {
          $columns = array(
            'site_id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 1),
            'name'    => array('type' => 'VARCHAR', 'constraint' => 256, 'default' => '', 'null' => false),
            'value'   => array('type' => 'TEXT'),
          );
          ee()->dbforge->add_field('id', TRUE);
          ee()->dbforge->add_field($columns);
          ee()->dbforge->create_table('dalle_settings', TRUE);
        }

        if( ! ee()->db->table_exists('dalle_images') )
        {
          $columns = array(
            'site_id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 1),
            'created' => array('type' => 'VARCHAR', 'constraint' => 256, 'default' => '', 'null' => false),
            'phrase'  => array('type' => 'VARCHAR', 'constraint' => 500, 'default' => '', 'null' => false),
            'file_id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 0),
            'base64'  => array('type' => 'MEDIUMBLOB'),
          );
          ee()->dbforge->add_field('id', TRUE);
          ee()->dbforge->add_field($columns);
          ee()->dbforge->create_table('dalle_images', TRUE);
        }

        if( ! ee()->db->table_exists('dalle_errors') )
        {
          $columns = array(
            'site_id' => array('type' => 'TINYINT', 'unsigned' => TRUE, 'default' => 1),
            'code'    => array('type' => 'VARCHAR', 'constraint' => 256, 'default' => '', 'null' => false),
            'message' => array('type' => 'TEXT'),
            'status'  => array('type' => 'VARCHAR', 'constraint' => 256, 'default' => '', 'null' => false),
            'created' => array('type' => 'INT', 'unsigned' => TRUE),
          );
          ee()->dbforge->add_field('id', TRUE);
          ee()->dbforge->add_field($columns);
          ee()->dbforge->create_table('dalle_errors', TRUE);
        }

        return true;
    }

    public function update($current = '')
    {
        // Runs migrations
        parent::update($current);

        return true;
    }

    public function uninstall()
    {
        parent::uninstall();

        $table_names = [
          'dalle_settings',
          'dalle_images',
          'dalle_errors',
        ];

        foreach($table_names as $name)
        {
          if (ee()->db->table_exists($name))
          {
            ee()->dbforge->drop_table($name);
          }
        }

        return true;
    }
}
