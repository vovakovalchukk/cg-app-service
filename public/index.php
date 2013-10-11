<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

use Slim\Slim;
use Slim\Route;
use Slim\Middleware\ContentTypes;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Hal\Renderer;
use Nocarrier\Hal;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$app = $serviceManager->get('Slim\Slim');
$app->add(new Renderer());

$fromXml = function($request) { return Hal::fromXml($request); };
$fromJson = function($request) {
    return ($request == '') ? $request : Hal::fromJson($request, PHP_INT_MAX);
};

$app->add(new ContentTypes(array(
    'application/hal+xml' => $fromXml,
    'application/hal+json' => $fromJson,
    'application/xml' => $fromXml,
    'application/json' => $fromJson
)));

$options = function (Route $route) use ($app) {
    $options = new Options($route, $app);
    $options->call();
};

foreach ($routes as $route => $request) {
    $route = $app->map($route, $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = array($request['via']);
    }
    call_user_func_array(array($route, 'via'), $request['via']);
}

$app->any('.+',function() use ($app) {
    $unusedMethods = new UnusedMethods($app);
    $unusedMethods->call();
});

$serviceManager->get('Di')->instanceManager()->addSharedInstance($app, 'Slim\Slim');
$app->run();

