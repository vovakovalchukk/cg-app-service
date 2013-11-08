<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Chef\Environment\Local;
use CG\Skeleton\Chef\Environment\Dual;

class EnvironmentFactory
{
    public static function build($environmentString)
    {
        return new $environmentString;
    }
}