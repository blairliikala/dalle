<?php

namespace Blairliikala\Dalle\Services;

class Images
{

  protected $settings = array();

  public function __construct()
  {
    if (empty($this->settings))
    {
      $this->settings = ee('dalle:settings')::get();
    }
  }


  // TODO.  This needs to be refactored for handling multiple images with same phrase.
  public function get(String $phrase, String $size, String|Bool $cache) : Array
  {

    $cache = ee('dalle:utilities')->getBool($cache);

    if ($cache)
    {
      $search_results = $this->searchPhrase($phrase);
    }

    if ($cache && $search_results->count() > 0)
    {
      // Get most recent phrased image.
      $image = end($search_results);
      $image = end($image);

      if ($image->file_id === 0)
      {
        // No file ID assigned if file wasn't created. So create a new one.
        $post_fields = array(
          'phrase' => $phrase,
          'size'   => $size,
        );
  
        list($file, $db) = $this->createImages($post_fields);
      }
      else 
      {
        $file = ee('Model')->get('File', $image->file_id)->first();
      }
    }

    if (!$cache OR $search_results->count() === 0)
    {
      $post_fields = array(
        'phrase' => $phrase,
        'size'   => $size,
      );

      list($file, $db) = $this->createImages($post_fields);
    }
    return [$file];
  }  


  private function createImages(Array $post_fields = array()) : Array
  {
    $post_fields['size'] = isset($post_fields['size']) ? $post_fields['size'] : '256x256';

    $images = ee('dalle:api')->create($post_fields);

    if (isset($images->error))
    {
      $message = isset($images->error->message) ? $images->error->message : '';
      $code = isset($images->error->code) ? $images->error->code : '';
      $type = isset($images->error->type) ? $images->error->type : '';

      ee('dalle:utilities')->logErrorLocally($message, $code, $type);
      return [$images, []];
    }

    if (isset($images->data))
    {
      foreach($images->data as $key=>$image)
      {
        $db = $this->saveImageToDB($post_fields['phrase'], $images->created, $image);
        $file = $this->addToFileManager($image, $post_fields, $image);

        if (isset($db->error))
        {
          ee('dalle:utilities')->logErrorLocally($db->error->message, $db->error->type);
          return $db;
        }

        if (isset($file->error))
        {
          ee('dalle:utilities')->logErrorLocally($db->error->message, $db->error->type);
          return $file;
        }

        $db->file_id = $file->getId();
        $db->save();

      }

    }

    return [$file, $db];

  }


  public function searchPhrase(String $phrase = '') : Object|NULL
  {
    return ee('Model')->get('dalle:Images')->filter('phrase', $phrase)->all();
  }


  private function saveImageToDB(String $phrase = '', Int $created = 0, Object $image = NULL) : Object
  {

    $db = ee('Model')->make('dalle:Images');

    $db->site_id = ee()->config->item('site_id') ?? 1;
    $db->created = $created ?? time();
    $db->phrase  = $phrase;
    $db->file_id = 0;
    $db->base64  = (isset($image->b64_json)) ? $image->b64_json : '';

    $result = $db->validate();

    if ($result->isValid())
    {
      $db->save();
      return $db;
    }
    else
    {
      return ee('dalle:utilities')->error('Dalle DB save validation error');
    }

  }


  // Currenty this only supports the local adaptor and local files.
  private function addToFileManager(Object $image, Array $post_fields) : Object
  {

    $upload_location_id = $this->getDestinationId();

    if ( ! $upload_location_id OR $upload_location_id === 0)
    {
      return ee('dalle:utilities')->error('No Upload directory was set.');
    }

    $destination = ee('Model')->get('UploadDestination', $upload_location_id)->first();
    $file_name = rand(10000000,99999999).".jpg";
    $title = substr($post_fields['phrase'], 0, 75);
    $filepath = $destination->server_path.$file_name;

    // Save File, base 64. Usually not used.
    if (isset($image->b64_json))
    {
      $image_data = base64_decode($image->b64_json);
      $source = imagecreatefromstring($image_data);
      $jpg = imagejpeg($source, $filepath, 100);
      $copy_result = $jpg;
    }

    // Save jpg.
    if (isset($image->url))
    {
      $copy_result = copy($image->url, $filepath);
    }

    if (!isset($copy_result))
    {
      return ee('dalle:utilities')->error('Unable to copy file.');
    }

    $file = ee('Model')->make('File');
    $file->upload_location_id = $upload_location_id;
    // $file->directory_id =
    $file->file_name = $file_name;
    $file->mime_type = 'image/jpeg';
    $file->file_size = filesize($filepath);
    $file->site_id = ee()->config->item('site_id');
    $file->upload_date = time();
    $file->title = $title;
    $file->credit = 'Made with Dall-E';
    $file->description = $post_fields['phrase'];
    $file->save();

    return $file;

  }


  private function getDestinationId() : Int
  {
    if (isset($this->settings['destination_id']) AND !empty($this->settings['destination_id']))
    {
      return $this->settings['destination_id'];
    }

    // No upload directories created, they have to make one first.
    $all = ee('Model')->get('UploadDestination')->all();
    if (empty($all))
    {
      return 0;
    }

    // If the theme was installed, use 4 "blog".  1-3 are other private things?
    $upload = ee('Model')->get('UploadDestination', 4)->first(); // First is avitars.
    if ($upload AND isset($upload->id))
    {
      return $upload->id;
    }

    // First could be Avitars, or one they create. This is all fallback though.
    $upload = ee('Model')->get('UploadDestination')->first(); 
    if ($upload AND isset($upload->id))
    {
      return $upload->id;
    }
    
  }

}