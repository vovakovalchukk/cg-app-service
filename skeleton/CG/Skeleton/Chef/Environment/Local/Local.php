<?php
namespace CG\Skeleton\Chef\Environment\Local;

use CG\Skeleton\Chef\EnvironmentInterface;

class Local implements EnvironmentInterface {

    public function getName()
    {
        return 'Local';
    }

    public function setupIp()
    {

    }
}
