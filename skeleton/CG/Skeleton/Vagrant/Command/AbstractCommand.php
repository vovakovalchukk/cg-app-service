<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

abstract class AbstractCommand implements CommandInterface
{
    const VAGRANT_PATH = '/tools/vagrant';

    public function run(Arguments $arguments, Config $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . static::VAGRANT_PATH);
        exec('git checkout ' . $config->getBranch() . ' > /dev/null;');
        $this->runVagrantCommand($arguments, $config);
        chdir($cwd);
        return true;
    }

    abstract protected function runVagrantCommand(Arguments $arguments, Config $config);
}