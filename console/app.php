<?php
chdir(dirname(__DIR__));

use Cilex\Application as Cilex;
use CG\Cilex\GenericCommand;

require_once 'application/bootstrap.php';
$commands = require_once 'config/console/commands.php';

$app = $di->get(Cilex::class, array("name" => ""));
foreach ($commands as $commandName => $command) {
    $app->command($di->newInstance(GenericCommand::class, array("commandName" => $commandName, "command" => $command)));
}
$app->run();
