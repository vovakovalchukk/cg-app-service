<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;

class Local extends Environment {

    public function getName()
    {
        return 'Local';
    }

    public function setupIp()
    {
        $ip = $this->getEnvironmentConfig()->getIp();
        while (!$ip) {
            $this->getConsole()->writeErrorStatus('IP address for ' . $this->getName() . ' environment is not set');
            $ip = $this->getConsole()->ask('What ip?');
        }
        $this->getConsole()->writeStatus('IP set to \'' . $ip . '\'');
        $this->getEnvironmentConfig()->setIp($ip);
    }

    public function setupHostname()
    {
        $hostname = $this->getEnvironmentConfig()->getHostname();
        while (!$hostname) {
            $this->getConsole()->writeErrorStatus('Application hostname is not set');
            $hostname = $this->getConsole()->ask('What url will your app be available at');
        }
        $this->getConsole()->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $this->getEnvironmentConfig()->setHostname($hostname);
    }

    protected function getIpsInUse()
    {
        // this is a local env specific thing, as the user is asked to choose one in local env.
    }
}
