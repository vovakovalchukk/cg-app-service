<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Chef\StartupCommand;
use CG\Skeleton\Chef\Hosts;


class Dual extends Environment {

    protected $name = 'Dual';
    protected $suffix = '.local';

    protected $environmentNodes = array('services', 'frontend', 'infrastructure');

    public function getName()
    {
        return $this->name;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setupIp(Startup $console)
    {
        $ipAddress = $this->getEnvironmentConfig()->getIp();
        $configuredHosts = $this->getHosts();

        while (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $console->writeErrorStatus('IP address is not set or is invalid');
            $chosenNode = $console->askWithOptions(
                'Which node would you like to run your app on?',
                $this->environmentNodes,
                $this->environmentNodes[0]
            );
            $ipAddress = $configuredHosts[$chosenNode]['ip'];
        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function setupHostname(Startup $console)
    {

    }
}
