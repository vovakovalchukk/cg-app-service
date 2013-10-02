<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class Up extends AbstractCommand
{
    public function getName()
    {
        return 'Start Virtual Machine';
    }

    protected function runVagrantCommand(Arguments $arguments, Config $config)
    {
        exec(' vagrant up ' . $config->getNode());
    }
}