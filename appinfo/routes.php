<?php

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'file#get', 'url' => '/d/{path}', 'verb' => 'GET', 'requirements' => array('path' => '.+')],
        ['name' => 'file#save', 'url' => '/api', 'verb' => 'POST', 'requirements' => array('path' => '.+')],
        ['name' => 'file#create', 'url' => '/api/create', 'verb' => 'POST'],
        ['name' => 'file#static', 'url' => '/sources/{path}', 'verb' => 'GET', 'requirements' => array('path' => '.+\.(json|zip|csh|wasm|psd|sketch|woff2|otf|ttf)')],
        //['name' => 'file#fonts', 'url' => '/sources/rsrc/fonts/{path}', 'verb' => 'GET', 'requirements' => array('path' => '.+')],
    ]
];
