<?php
namespace CG\Transaction\Predis;

use CG\Predis\Command\CachedScriptedCommandAbstract;

class ClearStaleTransaction extends CachedScriptedCommandAbstract
{
    protected function getKeysCount()
    {
        return 2;
    }
}