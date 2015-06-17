<?php
use CG\Slim\Module\Logging as LoggingModule;
use CG\Slim\NewRelic;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Validator;
use CG\Slim\Cache;
use CG\Slim\Versioning\Middleware as Versioning;
use CG\Slim\Itid\ItidInjector;
use CG\Slim\Etag;
use CG\Slim\Etag\ConfigFactory as EtagConfigFactory;
use CG\Middleware\Handler as MiddlewareHandler;

require_once dirname(__DIR__).'/application/bootstrap.php';
$routes = require_once dirname(__DIR__).'/config/routing.php';

$di->newInstance(Cache::class, ["app" => $app]);
$di->newInstance(LoggingModule::class)->register($app);
$app->add($di->get(ItidInjector::class));

$newRelic = $di->get(NewRelic::class, compact('app'));
$options = $di->get(Options::class, compact('app'));
$validator = $di->get(Validator::class, compact('app', 'di'));
$versioning = $di->get(Versioning::class);
$eTagConfigFactory = $di->get(EtagConfigFactory::class);
$eTag = $di->get(Etag::class, ['configFactory' => $eTagConfigFactory]);

$app->get(Versioning::VERSION_ROUTE, array($versioning, 'versionRoute'));
foreach ($routes as $route => $request) {
    $validator->setValidators($request);
    $versioning->setRouteVersion($request);
    $eTagConfigFactory->attachEtagConfig($request);

    $route = $app->map(
        $route,
        $newRelic,
        $versioning,
        $validator,
        $eTag,
        $options,
        $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = [$request['via']];
    }
    call_user_func_array([$route, 'via'], $request['via']);
}
$app->any('.+', $newRelic);

$di->get(MiddlewareHandler::class)->register(
    $app,
    [
        750 => $versioning
    ]
);

include_once dirname(__DIR__).'/config/DiSharedInstances.php';
$app->run();
fastcgi_finish_request();
