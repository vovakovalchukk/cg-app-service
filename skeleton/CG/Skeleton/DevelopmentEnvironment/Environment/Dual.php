<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\EnvironmentInterface;

class Dual implements EnvironmentInterface {

    public function getName()
    {
        return 'Dual';
    }

    public function setupIp(Startup $console)
    {

    }

    public function setupHostname(Startup $console)
    {

    }
}
