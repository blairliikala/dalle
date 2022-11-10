<?php

return [
    'author'            => 'Blair Liikala',
    'author_url'        => 'blairliikala.com',
    'name'              => 'Dall-e AI Image Creation',
    'description'       => 'Create images from the AI Dall-E',
    'version'           => '0.0.1',
    'namespace'         => 'Blairliikala\Dalle',
    'settings_exist'    => true,
    'requires'          => [
        'php' => '8.0',
        'ee'  => '7.1.6'
    ],
    'services' => [
        'api' => 'Services\Api',
        'images' => 'Services\Images',
        'settings' => 'Services\Settings',
        'utilities' => 'Services\Utilities',
	],
    'models' => [
        'Images' => 'Models\\Images',
        'Settings' => 'Models\\Settings',
        'Errors' => 'Models\\Errors',
	],
    /* For Later.
    'fieldtypes' => [
        'mux_live' => [
          'name' => 'Dalle Image',
          'compatibility' => 'text',
        ],
    ],
    'commands' => [
        'mux:api' => 'Mux\Commands\Api_methods',
    ]
    */
];