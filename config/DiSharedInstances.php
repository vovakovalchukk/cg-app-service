<?php
$di->instanceManager()->addSharedInstance($app, Slim::class);
$di->instanceManager()->addSharedInstance($app->request()->headers, 'SlimRequestHeaders');
$di->instanceManager()->addSharedInstance($app->response()->headers, 'SlimResponseHeaders');

