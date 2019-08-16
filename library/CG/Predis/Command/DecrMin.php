<?php
namespace CG\Predis\Command;

use Predis\Command\ScriptedCommand;

class DecrMin extends ScriptedCommand
{
    public function getScript()
    {
        return <<<LUA
if redis.call('exists', KEYS[1]) == 0 then
    return nil;
end

if redis.call('get', KEYS[1]) <= ARGV[1] then
    redis.call('set', KEYS[1], ARGV[1])
    return nil
end

return redis.call('decr', KEYS[1])
LUA;
    }

    protected function getKeysCount()
    {
        return 1;
    }
}