<?php

namespace Blairliikala\Dalle\Services;

class Api {

    private $base = '';

    public function __construct()
    {
      $this->settings = ee('dalle:settings')::get();
      $this->base = 'https://api.openai.com/v1';
    }


    public function create(Array $post_fields = array()) : Object
    {
      $url = $this->base.'/images/generations';
      $post_fields = array(
        "prompt" => substr($post_fields['phrase'], 0, 1000),
        "n"      => $post_fields['n'] ?? 1,
        "size"   => $post_fields['size'] ?? $this->settings['size'],
        "response_format" => $post_fields['response_format'] ?? $this->settings['response_format'],
        // "user" => "", // For tracking abuse?
      );
      return $this->http($url, 'POST', $post_fields);
    }


    public function http(String $url = '', String $type = 'GET', Array $post_fields = array()) : Object|Null
    {
      if (empty($url))
      {
        return ee('dalle:utilities')->error('No URL provided to get');
      }

      if (!isset($this->settings['token']) OR empty($this->settings['token']))
      {
        return ee('dalle:utilities')->error('A valid token is required.  Please create one in the Open AI website, and add it to the add-on settings.');
      }

      // cURL is an ExpressionEngine requirement so we can assume it is installed.
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_TIMEOUT => 200,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $type,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($post_fields),
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer '.$this->settings['token'],
          'Content-Type: application/json'
        ),
      ));

      $response_string = curl_exec($curl);
      $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      $error_string = curl_error($curl);

      curl_close($curl);

      if (!empty($error_string)) {
        ee('dalle:utilities')->logErrorLocally($error_string, "api_request_error", strval($code));
      }

      return json_decode($response_string);
    }

}