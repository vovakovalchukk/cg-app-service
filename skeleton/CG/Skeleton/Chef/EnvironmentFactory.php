<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Console\Startup;

class EnvironmentFactory
{
    const ENVIRONMENT_NAMESPACE = 'CG\Skeleton\Chef\Environment\\';

    public static function build(Startup $console, $environmentString)
    {
        $environmentClass = self::ENVIRONMENT_NAMESPACE . $environmentString;
        return new $environmentClass($console);
    }
}