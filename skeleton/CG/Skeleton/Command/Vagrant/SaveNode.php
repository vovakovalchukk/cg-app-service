<?php
namespace CG\Skeleton\Command\Vagrant;

use CG\Skeleton\Command;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class SaveNode implements Command
{
    public function getName()
    {
        return 'Create / Update node data';
    }

    public function run(Arguments $arguments, Config $config)
    {
        return true;
    }
}