<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Console\Startup;
use CG\Skeleton\Config;

class EnvironmentFactory
{
    const ENVIRONMENT_NAMESPACE = 'CG\Skeleton\Chef\Environment\\';

    public static function build(Startup $console, $environmentString, Config $config)
    {
        $environmentClass = static::ENVIRONMENT_NAMESPACE . $environmentString;
        return new $environmentClass($console, $config);
    }
}