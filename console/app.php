<?php
chdir(dirname(__DIR__));

use CG\Slim\Console;

require_once 'application/bootstrap.php';
$routes = require_once 'config/console/routing.php';

$console = new Console();
$console->mapRequest();
foreach ($routes as $route => $request) {
    $route = $app->get($console->routeToSlimRoute($route), $request["controllers"])->name($request["name"]);
}
$app->run();
