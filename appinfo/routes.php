<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'section#index', 'url' => '/api/sections', 'verb' => 'GET'],
        ['name' => 'section#create', 'url' => '/api/sections', 'verb' => 'POST'],
        ['name' => 'section#update', 'url' => '/api/sections/{id}', 'verb' => 'PUT'],
        ['name' => 'section#destroy', 'url' => '/api/sections/{id}', 'verb' => 'DELETE'],
        ['name' => 'section#reorder', 'url' => '/api/sections/reorder', 'verb' => 'POST'],
    ]
];