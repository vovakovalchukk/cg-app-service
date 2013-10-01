<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

use Slim\Slim;

require_once 'application/bootstrap.php';
require_once 'config/routing.php';

$app = new Slim();

foreach ($routes as $route => $request) {
    $route = $app->map($route, $request["controllers"])->name($request["name"]);
    if (!is_array($request['via'])) {
        $request['via'] = array($request['via']);
    }
    call_user_func_array(array($route, 'via'), $request['via']);
}

$serviceManager->setService('Slim\Slim', $app);
$app->run();

