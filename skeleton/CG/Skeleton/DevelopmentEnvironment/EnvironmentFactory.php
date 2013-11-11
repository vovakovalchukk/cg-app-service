<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Console\Startup;
use CG\Skeleton\Config;

class EnvironmentFactory
{
    const ENVIRONMENT_NAMESPACE = 'CG\Skeleton\Chef\Environment\\';

    public static function build(Startup $console, Config $config)
    {
        $environmentClass = static::ENVIRONMENT_NAMESPACE . $config->getEnvironment();
        return new $environmentClass($console, $config);
    }
}