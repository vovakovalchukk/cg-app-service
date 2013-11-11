<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Chef\StartupCommand;
use CG\Skeleton\Chef\Hosts;


class Dual extends Environment {

    protected $nodes = array('frontend', 'services', 'infrastructure');

    public function getName()
    {
        return 'Dual';
    }

    public function setupIp(Startup $console)
    {
        $ipAddress = $this->getEnvironmentConfig()->getIp();

        $ipAddress = $console->askWithOptions('Which node would you like to run your app on?', $this->nodes, 'services');

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function setupHostname(Startup $console)
    {

    }
}
