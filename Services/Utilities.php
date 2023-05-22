<?php

namespace Blairliikala\Dalle\Services;

class Utilities
{

  public $settings = array();

  public function __construct()
  {
    if (empty($this->settings)) {
      $this->settings = ee('dalle:settings')::get();
    }
  }


  public function error(string|array $message = ''): object
  {

    if (empty($message)) {
      $message = 'No Message Provided.';
    }

    if (gettype($message) == 'array') {
      $message = implode('<br>', $message);
    }

    return (object) array(
      'error' => (object) array(
        'type' => 'ee_addon',
        'message' => $message,
      )
    );

  }

  public function logError($error): object
  {
    $message = $error->message ?? 'No message provided.';

    $log = ee('Model')->make('CpLog');
    $log->act_date = ee()->localize->now;
    $log->action = "Dall-E: " . $message;
    if (isset(ee()->session)) {
      $log->member_id = ee()->session->userdata('member_id');
      $log->username = ee()->session->userdata('username');
      $log->ip_address = ee()->session->userdata('ip_address');
    } else {
      $log->member_id = 0;
      $log->username = 'N/A';
      $log->ip_address = '0.0.0.0';
    }
    $log->save();
    return $log;
  }


  public function logErrorLocally(string|NULL $message = '', string|NULL $code = '', string|NULL $status = ''): object|NULL
  {
    $error = ee('Model')->make('dalle:Errors');
    $error->set([
      'message' => $message ?? '',
      'code' => $code ?? '',
      'status' => $status ?? '',
      'site_id' => ee()->config->item('site_id'),
      'created' => time(),
    ]);
    $result = $error->validate();
    if ($result->isValid()) {
      $error->save();
      return $error;
    } else {
      $errors = $result->getAllErrors();
      ee()->load->library('logger');
      ee()->logger->developer('Dall-E Error: Failed creating log entry. ' . json_encode($errors));
      return NULL;
    }
  }


  public function getBool($value): bool
  {

    switch (true) {
      case is_bool($value):
        return $value;

      case gettype($value) === 'string':
        $yes = ["y", "yes", "true"];
        return in_array(strtolower($value), $yes) ? TRUE : FALSE;

      case gettype($value) === 'number':
        return $value > 0 ? TRUE : FALSE;

      case gettype($value) === 'array' or gettype($value) === 'object':
        return count($value) > 0 ? TRUE : FALSE;

      case gettype($value) === NULL:
        return FALSE;

      default:
        return FALSE;
    }

  }


  public function generateSidebar(): object
  {
    $sidebar = ee('CP/Sidebar')->make();
    $sidebar->addItem(lang('generate'), ee('CP/URL', 'addons/settings/dalle'));
    $sidebar->addItem(lang('settings'), ee('CP/URL', 'addons/settings/dalle/settings'));
    $sidebar->addItem(lang('error_log'), ee('CP/URL', 'addons/settings/dalle/errorlog'));
    //$sidebar->addItem(lang('images'), ee('CP/URL', 'addons/settings/dalle/list'));
    $sidebar->render();

    ee()->cp->header = array(
      'toolbar_items' => array(
        'settings' => array(
          'href' => ee('CP/URL')->make('addons/settings/dalle/settings'),
          'title' => lang('settings')
        )
      )
    );
    return $sidebar;
  }

  public function outputJSON($data)
  {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

}