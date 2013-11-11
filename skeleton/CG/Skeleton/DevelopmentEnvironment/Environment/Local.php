<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;

class Local extends Environment {

    public function getName()
    {
        return 'Local';
    }

    public function setupIp(Startup $console)
    {
        $ip = $this->getEnvironmentConfig()->getIp();
        while (!$ip) {
            $console->writeErrorStatus('IP address for ' . $this->getName() . ' environment is not set');
            $ip = $console->ask('What ip?');
        }
        $console->writeStatus('IP set to \'' . $ip . '\'');
        $this->getEnvironmentConfig()->setIp($ip);
    }

    public function setupHostname(Startup $console)
    {
        $hostname = $this->getEnvironmentConfig()->getHostname($this->getConfig());
        while (!$hostname) {
            $console->writeErrorStatus('Application hostname is not set');
            $hostname = $console->ask('What url will your app be available at');
        }
        $console->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $this->getEnvironmentConfig()->setHostname($hostname);
    }

    protected function getIpsInUse()
    {
        // this is a local env specific thing, as the user is asked to choose one in local env.
    }
}
