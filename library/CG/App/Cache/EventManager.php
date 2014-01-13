<?php
namespace CG\App\Cache;

use CG\Cache\EventManagerInterface;
use Zend\EventManager\GlobalEventManager;

class EventManager implements EventManagerInterface
{
    public function trigger($event, $target, $argv = array())
    {
        return GlobalEventManager::trigger($event, $target, $argv);
    }
}