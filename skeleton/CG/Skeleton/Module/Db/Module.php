<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfigClass()
    {
        return 'CG\Skeleton\Module\BaseConfig';
    }
}