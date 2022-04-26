<?php

return [
    'routes' => [
        ['name' => 'file#get', 'url' => '/io/{path}', 'verb' => 'GET', 'requirements' => array('path' => '.+')],
        ['name' => 'file#put', 'url' => '/io/{path}', 'verb' => 'POST', 'requirements' => array('path' => '.+')],
        ['name' => 'file#create', 'url' => '/create', 'verb' => 'POST'],
        //['name' => 'file#fonts', 'url' => '/sources/rsrc/fonts/{path}', 'verb' => 'GET', 'requirements' => array('path' => '.+')],
    ]
];
