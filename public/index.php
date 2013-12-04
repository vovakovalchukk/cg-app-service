<?php
chdir(dirname(__DIR__));

use CG\Slim\ContentTypes;
use CG\Slim\NewRelic;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Renderer;
use CG\Slim\Validator;
use CG\Slim\VndError\VndError;
use CG\Slim\Versioning\Middleware as Versioning;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$newRelic = $di->get(NewRelic::class, compact('app'));
$options = $di->get(Options::class, compact('app'));
$unusedMethods = $di->get(UnusedMethods::class, compact('app'));
$validator = $di->get(Validator::class, compact('app', 'di'));
$versioning = $di->get(Versioning::class);

$app->get(Versioning::VERSION_ROUTE, array($versioning, 'versionRoute'));
foreach ($routes as $route => $request) {
    $validator->setValidators($request);
    $versioning->setRouteVersion($request);

    $route = $app->map(
        $route,
        $newRelic,
        $versioning,
        $validator,
        $options,
        $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = [$request['via']];
    }
    call_user_func_array([$route, 'via'], $request['via']);
}
$app->any('.+', $newRelic, $unusedMethods);

$app->add($di->get(ContentTypes::class));
$app->add($di->get(VndError::class));
$app->add($versioning);
$app->add($di->get(Renderer::class));

include_once 'config/DiSharedInstances.php';
$app->run();
