<?php
$routes = array(
    '/' => array (
        'controllers' => function() use ($serviceManager) {
            $controller = $serviceManager->get('Index');
            $controller->index();
        },
        'via' => 'GET',
        'name' => 'index'
    ),
);
