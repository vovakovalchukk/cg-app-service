<?php
namespace CG\Transaction\Predis;

use CG\Predis\Command\CachedScriptedCommandAbstract;

class ClearStaleTransaction extends CachedScriptedCommandAbstract
{
    protected const LUA_FILE_DIRECTORY = PROJECT_ROOT . '/lua/';

    protected function getKeysCount()
    {
        return 2;
    }
}