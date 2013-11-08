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
        echo "Hello World\n";
    }

    protected function getIpsInUse()
    {
        // this is a local env specific thing, as the user is asked to choose one in local env.
    }
}
