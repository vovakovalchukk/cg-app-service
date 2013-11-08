<?php
namespace CG\Skeleton\Chef\Environment;

use CG\Skeleton\Chef\AbstractEnvironment;
use CG\Skeleton\Config;

class Local extends AbstractEnvironment {

    public function getName()
    {
        return 'Local';
    }

    public function setupIp(Config $config)
    {
        $ip = $config->getIp();
        while (!$ip) {
            $this->getConsole()->writeErrorStatus('IP address for ' . $this->getName() . ' environment is not set');
            $ip = $this->getConsole()->ask('What ip?');
        }
        $this->getConsole()->writeStatus('IP set to \'' . $ip . '\'');
        $config->setIp($ip);
    }

    protected function getIpsInUse()
    {
        // this is a local env specific thing, as the user is asked to choose one in local env.
    }
}
