<?php
namespace CG\Skeleton\Environment\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class PushInfrastructure implements CommandInterface
{
    public function getName()
    {
        return 'Push Infrastructure changes to bitbucket';
    }

    public function run(Arguments $arguments, Config $config, Environment $environment)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath());
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        passthru('git push origin ' . $config->getBranch());
        chdir($cwd);
    }
}