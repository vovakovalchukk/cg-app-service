<?php
chdir(dirname(__DIR__));

use Slim\Slim;
use CG\Slim\ContentTypes;
use CG\Slim\NewRelic;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Renderer;
use CG\Slim\Validator;
use CG\Slim\VndError\VndError;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$di = $serviceManager->get('Di');
$app = $serviceManager->get(Slim::class);

$newRelic = $di->get(NewRelic::class, compact('app'));
$options = $di->get(Options::class, compact('app'));
$unusedMethods = $di->get(UnusedMethods::class, compact('app'));
$validator = $di->get(Validator::class, compact('app', 'di'));

foreach ($routes as $route => $request) {
    $validator->setValidators($request);

    $route = $app->map(
        $route,
        $newRelic,
        $validator,
        $options,
        $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = [$request['via']];
    }
    call_user_func_array([$route, 'via'], $request['via']);
}
$app->any('.+', $unusedMethods);

$app->add($di->get(ContentTypes::class));
$app->add($di->get(VndError::class));
$app->add($di->get(Renderer::class));

$di->instanceManager()->addSharedInstance($app, Slim::class);
$app->run();