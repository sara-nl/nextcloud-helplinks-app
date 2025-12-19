<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'section#index', 'url' => '/api/sections', 'verb' => 'GET'],
        ['name' => 'section#create', 'url' => '/api/sections', 'verb' => 'POST'],
        ['name' => 'section#update', 'url' => '/api/sections/{id}', 'verb' => 'PUT'],
        ['name' => 'section#destroy', 'url' => '/api/sections/{id}', 'verb' => 'DELETE'],
        ['name' => 'section#reorder', 'url' => '/api/sections/reorder', 'verb' => 'POST'],
        ['name' => 'section#saveSettings', 'url' => '/api/settings', 'verb' => 'POST'],
    ],
    'ocs' => [
        ['name' => 'api#getSections', 'url' => '/api/v1/sections', 'verb' => 'GET'],
        ['name' => 'api#getSection', 'url' => '/api/v1/sections/{id}', 'verb' => 'GET'],
        ['name' => 'api#createSection', 'url' => '/api/v1/sections', 'verb' => 'POST'],
        ['name' => 'api#updateSection', 'url' => '/api/v1/sections/{id}', 'verb' => 'PUT'],
        ['name' => 'api#deleteSection', 'url' => '/api/v1/sections/{id}', 'verb' => 'DELETE'],
        ['name' => 'api#getSettings', 'url' => '/api/v1/settings', 'verb' => 'GET'],
        ['name' => 'api#updateSettings', 'url' => '/api/v1/settings', 'verb' => 'PUT'],
    ]
];