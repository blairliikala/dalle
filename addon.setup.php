<?php

return [
    'author'            => 'Blair Liikala',
    'author_url'        => 'blairliikala.com',
    'name'              => 'Dall-e AI Image Creation',
    'description'       => 'Create images from the AI Dall-E',
    'version'           => '0.0.2',
    'namespace'         => 'Blairliikala\Dalle',
    'settings_exist'    => true,
    'requires'          => [
        'php' => '8.0',
        'ee'  => '7.2.0'
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
];