<?php
use CG\Slim\ContentTypes;
use CG\Slim\Module\Logging as LoggingModule;
use CG\Slim\NewRelic;
use CG\Slim\Rest\Options;
use CG\Slim\Rest\UnusedMethods;
use CG\Slim\Renderer;
use CG\Slim\Validator;
use CG\Slim\VndError\VndError;
use CG\Slim\Cache;
use CG\Slim\Versioning\Middleware as Versioning;
use CG\Slim\HeadRequest\Middleware as HeadRequest;
use CG\Slim\Created\Created as Created;
use CG\Slim\Itid\ItidInjector;
use CG\Slim\Usage\Endpoint as UsageEndpoint;
use CG\Slim\Usage\Count as UsageCount;
use CG\Slim\Etag;
use CG\Slim\Etag\ConfigFactory as EtagConfigFactory;
use CG\Slim\Nginx\Cache\Invalidator as NginxInvalidator;

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

    if(!isset($request["eTag"]) && isset($request["entityRoute"])) {
        $request["eTag"] = $routes[$request["entityRoute"]]["eTag"];
    }
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

$app->add($di->get(Created::class));
$app->add($di->get(UsageEndpoint::class));
$app->add($di->get(UsageCount::class));
$app->add($di->get(NginxInvalidator::class));
$app->add($di->get(ContentTypes::class));
$app->add($di->get(VndError::class));
$app->add($di->get(UnusedMethods::class));
$app->add($di->get(HeadRequest::class));
$app->add($versioning);
$app->add($di->get(Renderer::class));
include_once dirname(__DIR__).'/config/DiSharedInstances.php';
$app->run();
