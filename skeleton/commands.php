<?php
$commands = new SplObjectStorage();
$commands->attach(new CG\Skeleton\Command\Vagrant\SaveNode());
return $commands;