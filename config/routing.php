<?php
use Slim\Slim;
use CG\Controllers\RestExample;

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
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(RestExample::class);

            $app->view()->set(
                'RestResponse',
                $controller->$method()
            );
        },
        "via" => array(
            'GET','OPTIONS'
        ),
        "name" => "restExample"
    )
);
