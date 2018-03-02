<?php
namespace CG\Transaction\Predis;

use Predis\Command\ScriptedCommand;

class ClearTransaction extends ScriptedCommand
{
    protected function getKeysCount()
    {
        return 2;
    }

    public function getScript()
    {
        return <<<SCRIPT
if !redis.call('EXISTS', KEYS[1]) then
    return redis.call('DEL', KEYS[2])
else
    return 0
end
SCRIPT;
    }
}