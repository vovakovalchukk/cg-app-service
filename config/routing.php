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
    "/rest" => array (
        "controllers" => function() use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get('Slim\Slim');
            $method = $app->request()->getMethod();

            $controller = $di->get('RestExample');

            $app->view()->set(
                'Hal',
                $controller->$method()
            );
        },
        "via" => array(
            'GET','OPTIONS'
        ),
        "name" => "restExample"
    )
);
