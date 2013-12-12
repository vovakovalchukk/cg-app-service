<?php
namespace CG\Skeleton\Module;

use Zend\Config\Config as ZendConfig;

class BaseConfig extends ZendConfig
{
    const ENABLED = 'enabled';

    public function isEnabled()
    {
        return (boolean) $this->get(static::ENABLED, false);
    }

    public function setEnabled($enabled)
    {
        $this->offsetSet(static::ENABLED, (boolean) $enabled);
    }

    public function setComposerRequire($name, $version)
    {
        $this->offsetSet($name, $version);
    }

    public function removeComposerRequire($name)
    {
        $this->offsetUnset($name);
    }
}