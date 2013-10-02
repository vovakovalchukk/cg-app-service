<?php
namespace CG\Skeleton\Module;

use Zend\Config\Config as ZendConfig;

class Config extends ZendConfig
{
    public function __construct(array $config, $allowModifications = false)
    {
        foreach ($config as $entry => $value) {
            if (!is_array($value)) {
                continue;
            }

            $configClass = $this->getConfigClass($entry);
            $config[$entry] = new $configClass($value, $allowModifications);
        }
        parent::__construct($config, $allowModifications);
    }

    protected function getConfigClass($name)
    {
        $configClass = __NAMESPACE__ . '\\' . $name . '\\Config';
        if (!class_exists($configClass)) {
            $configClass = 'Zend\\Config\\Config';
        }
        return $configClass;
    }

    public function getModule($name)
    {
        $configClass = $this->getConfigClass($name);
        return $this->get($name, new $configClass(array(), $this->allowModifications));
    }
}