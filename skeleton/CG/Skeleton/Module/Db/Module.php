<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Node;

class Module extends AbstractModule implements EnableInterface, ConfigureInterface
{
    public function getModuleName()
    {
        return 'Db';
    }

    public function getConfigClass()
    {
        return 'CG\Skeleton\Module\BaseConfig';
    }

    public function enable(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        if ($moduleConfig->isEnabled()) {
            return;
        }
        $moduleConfig->setEnabled(true);
        $this->configure($arguments, $config, $moduleConfig);
    }

    public function configure(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);

        $this->applyConfiguration($arguments, $config, $moduleConfig);
    }

    public function applyConfiguration(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);

        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig);
        chdir($cwd);
    }

    protected function updateNode(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        $nodeFile = Chef::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);
        $node->setKey('database|enabled', $moduleConfig->isEnabled());

        if ($moduleConfig->isEnabled()) {
            $node->setKey(
                'cg|capistrano|' . $config->getNode() . '|symlinks|config/autoload/database.local.php',
                'config/autoload/database.local.php'
            );
        } else {
            $node->removeKey('cg|capistrano|' . $config->getNode() . '|symlinks|config/autoload/database.local.php');
            $node->removeKey('database');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "SKELETON: Updated node ' . $config->getNode() . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }
}