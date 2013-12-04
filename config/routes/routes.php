<?php
use CG\Controllers\RestExample;
use CG\InputValidation\RestExample\Filter;

return array(
    '/' => array (
        'controllers' => function() use ($serviceManager) {
                $controller = $serviceManager->get('Index');
                $controller->index();
            },
        'via' => 'GET',
        'name' => 'index'
    ),
    "/rest" => array (
        "validation" => [
            "flatten" => true,
            "filterRules" => Filter::class
        ],
        "controllers" => function() use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(RestExample::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        "via" => ['GET','OPTIONS'],
        "name" => "restExample"
    )
);
