<?php
chdir(dirname(__DIR__));

use Slim\Slim;
use Slim\Middleware\ContentTypes;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Hal\Renderer;
use Nocarrier\Hal;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$di = $serviceManager->get('Di');
$app = $serviceManager->get(Slim::class);
$app->add($di->get(Renderer::class));

$fromXml = function($request) { return Hal::fromXml($request); };
$fromJson = function($request) {
    return ($request == '') ? $request : Hal::fromJson($request, PHP_INT_MAX);
};

$app->add(
    $di->get(
        ContentTypes::class,
        array(
            'settings' => array(
                'application/hal+xml' => $fromXml,
                'application/hal+json' => $fromJson,
                'application/xml' => $fromXml,
                'application/json' => $fromJson
            )
        )
    )
);

$options = $di->get(Options::class, array('app' => $app));

$unusedMethods = $di->get(UnusedMethods::class, array('app' => $app));
$app->any('.+', $unusedMethods);

foreach ($routes as $route => $request) {
    $route = $app->map(
        $route,
        $options,
        $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = array($request['via']);
    }
    call_user_func_array(array($route, 'via'), $request['via']);
}

$di->instanceManager()->addSharedInstance($app, Slim::class);
$app->run();

