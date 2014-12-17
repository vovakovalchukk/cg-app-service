<?php
chdir(dirname(__DIR__));

use Cilex\Application as Cilex;
use CG\Cilex\GenericCommand;
use CG\Cilex\ServiceProvider\Bootstrap;

require_once 'application/bootstrap.php';
$commands = require_once 'config/console/commands.php';

/**
 * @var Cilex $app
 */
$app = $di->get(Cilex::class);
$app->register($di->get(Bootstrap::class));
foreach ($commands as $commandName => $command) {
    $app->command($di->newInstance(GenericCommand::class, array("commandName" => $commandName, "command" => $command)));
}
$app->run();
