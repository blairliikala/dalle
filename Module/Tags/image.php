<?php

namespace Blairliikala\Dalle\Module\Tags;

use ExpressionEngine\Service\Addon\Controllers\Tag\AbstractRoute;

class Image extends AbstractRoute
{
  // Example tag: {exp:dalle:example_tag}
  public function process()
  {
    $tagdata  = ee()->TMPL->tagdata;
    $variables = array();

    if (!$tagdata)
    {
      return '';
    }

    $phrase = ee()->TMPL->fetch_param('phrase', ''); // false is default value.
    $size   = ee()->TMPL->fetch_param('size', '256x256');
    $cache  = ee()->TMPL->fetch_param('cache', true);
    // $id = ee()->TMPL->fetch_param('id'); // ID of the row for prev created images?

    // Phrase is required by API.
    if (empty($phrase))
    {
      return '';
    }

    list($file) = ee('dalle:images')->get($phrase, $size, $cache);

    if (isset($file->error))
    {
      $variables = array(
        'url' => '',
        'id' => '',
        'phrase' => '',
        'error' => isset($file->error->message) ? $file->error->message : '',
      );
    }

    $variables = array(
      'url' => !isset($file->error) ? $file->getAbsoluteURL() : '',
      'id' => !isset($file->error) ? $file->getId() : '',
      'phrase' => $phrase,
      'error' => isset($file->error->message) ? $file->error->message : '',
    );

    return ee()->TMPL->parse_variables($tagdata, array($variables));
  }
}
