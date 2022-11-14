<?php

namespace Blairliikala\Dalle\Services;

class Settings {

    public static $settings = array();

    public static function get() : Array
    {

      if (empty(self::$settings)) {
        $results = ee('Model')
                    ->get('dalle:Settings')
                    ->filter('site_id', ee()->config->item('site_id'))
                    ->all()
                    ->getDictionary('name', 'value');

        if (!$results)
        {
          $results = array();
        }

        // Defaults, if more happen, move to file.
        $defaults = array(
          "size" => "256x256",
          "response_format" => "url",// b64_json or url.
        );

        $all_settings = array_merge($defaults, $results);

        self::$settings = $all_settings;
      }

      return self::$settings;

    }


    public function save(Array $settings = array()) : Array
    {

      if (empty($settings))
      {
        return [FALSE, ee('dalle:utilities')->error('No settings to update.')];
      }

      $errors = array();

      foreach($settings as $name=>$value)
      {

        $row = ee('Model')->get('dalle:Settings')->filter('name', $name)->first();

        if (!$row)
        {
          $row = ee('Model')->make('dalle:Settings');
          $row->set(array(
            'name' => $name,
            'site_id' => ee()->config->item('site_id'),
          ));
        }

        $row->value = $value;

        $result = $row->validate();

        if ($result->isValid())
        {
          $row->save();
          //$this->purgeUnusedSettings($settings);
        }
        else
        {
          $all = $row->getAllErrors();
          $current_errors = array_map('current', $all);
          $errors[] = ee('dalle:utilities')->error($current_errors);
        }

      }

      if ( ! empty($errors))
      {
        return [FALSE, $errors];
      }      

      return [TRUE, array()];

    }


    public function purgeUnusedSettings(Array $settings = array())
    {

      if (empty($settings))
      {
        return;
      }

      $existing = ee('Model')->get('dalle:Settings')->all();

      foreach($existing as $row)
      {
        if ( ! in_array($row->name, array_keys($settings)))
        {
          $row->delete();
        }
      }

    }
}