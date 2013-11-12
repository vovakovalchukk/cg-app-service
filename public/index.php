<?php
chdir(dirname(__DIR__));

use Slim\Slim;
use CG\Slim\ContentTypes;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Renderer;
use CG\Slim\Validator;
use CG\Slim\VndError\VndError;
use Nocarrier\Hal;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$di = $serviceManager->get('Di');
$app = $serviceManager->get(Slim::class);

$options = $di->get(Options::class, array('app' => $app));

$unusedMethods = $di->get(UnusedMethods::class, array('app' => $app));
$app->any('.+', $unusedMethods);

$validator = $di->get(Validator::class);
foreach ($routes as $route => $request) {
    if (isset($request["validation"])) {
        $validator->attach($request["validation"]["flatten"], $route, $request["validation"]["dataRules"],
            $request["validation"]["filterRules"]);
    }
    $route = $app->map(
        $route,
        $options,
        $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = array($request['via']);
    }
    call_user_func_array(array($route, 'via'), $request['via']);
}
$app->add($di->get(Validator::class));
$app->add($di->get(ContentTypes::class));
$app->add($di->get(VndError::class));
$app->add($di->get(Renderer::class));

$di->instanceManager()->addSharedInstance($app, Slim::class);
$app->run();

