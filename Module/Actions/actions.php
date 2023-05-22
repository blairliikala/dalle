<?php

namespace Blairliikala\Dalle\Module\Actions;

use ExpressionEngine\Service\Addon\Controllers\Action\AbstractRoute;

// TODO.  Prompts over GET would need to be URL-decoded.

class Actions extends AbstractRoute
{
    public function process()
    {

        $data = [];

        if ($_REQUEST === 'POST') {

            $method = $_POST['method'] ?? null;

            if (!$method) {
                $this->output(ee('dalle:utilities')->error('POST request does not have a method.'));
            }

            $post = json_decode(file_get_contents('php://input'), TRUE);

            if (!$post) {
                $this->output(ee('dalle:utilities')->error('POST request not formatted for JSON.'));
            }

            $data = [
                'phrase' => $post['phrase'] ?? null,
                'size' => $post['size'] ?? '',
                'cache' => $post['cache'] ?? true,
            ];

        } else {

            $method = $_GET['method'] ?? null;

            if (!$method) {
                $this->output(ee('dalle:utilities')->error('GET request does not have a method.'));
            }

            $data = [
                'phrase' => $_GET['phrase'] ?? null,
                'size' => $_GET['size'] ?? '',
                'cache' => $_GET['cache'] ?? true,
            ];

        }

        switch (true) {
            case $method === 'create':

                // Phrase is required by API.
                if (empty($data['phrase'])) {
                    $this->output(ee('dalle:utilities')->error('No phrase included.'));
                }

                list($file) = ee('dalle:images')->get($data['phrase'], $data['size'], (bool) $data['cache']);
                $this->output($file);
                break;

            case $method === 'get':
                if (!$data['phrase']) {
                    $dalleFiles = ee('Model')->get('dalle:Images')->all();
                    $images = [];
                    foreach($dalleFiles as $dalleFile) {
                        list($file) = ee('dalle:images')->get($dalleFile->phrase, '256x256', true);
                        $images[] = array_merge([
                            'url' => !isset($file->error) ? $file->getAbsoluteURL() : '',
                            'id' => !isset($file->error) ? $file->getId() : '',
                            'phrase' => $dalleFile->phrase,
                            'size' => $file->file_hw_original,
                            'error' => isset($file->error->message) ? $file->error->message : '',
                        ], $file->toArray());
                    }
                    $this->output($images);
                }
                list($file) = ee('dalle:images')->get(urldecode($data['phrase']), $data['size'], (bool) $data['cache']);
                $this->output($file);
                break;

            default:
                $this->output(ee('dalle:utilities')->error('No method matches.'));

        }
    }

    private function output($output)
    {
        header('Content-Type: application/json');
        echo json_encode($output);
        exit;
    }
}