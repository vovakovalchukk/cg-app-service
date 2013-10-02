<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\Command;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class Up implements Command
{
    const VAGRANT_PATH = '/tools/vagrant';

    public function getName()
    {
        return 'Start Virtual Machine';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . static::VAGRANT_PATH);
        exec('git checkout ' . $config->getBranch() . ' > /dev/null; vagrant up ' . $config->getNode());
        chdir($cwd);
        return true;
    }
}