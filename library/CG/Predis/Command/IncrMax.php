<?php
namespace CG\Predis\Command;

use Predis\Command\ScriptedCommand;

class IncrMax extends ScriptedCommand
{
    public function getScript()
    {
        return <<<LUA
if redis.call('exists', KEYS[1]) == 0 or redis.call('get', KEYS[1]) < ARGV[1] then
    return redis.call('incr', KEYS[1])
else
    return nil;
end
LUA;
    }

    protected function getKeysCount()
    {
        return 1;
    }
}