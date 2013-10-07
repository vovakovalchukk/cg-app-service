<?php
namespace CG\Skeleton\Module\Redis;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ApplyConfigurationInterface;
use CG\Skeleton\Module\DisableInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Node;

class Module extends AbstractModule implements EnableInterface, ApplyConfigurationInterface, DisableInterface
{
    use \CG\Skeleton\ComposerTrait;

    public function getModuleName()
    {
        return 'Redis';
    }

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }

    public function applyConfiguration(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig);
        chdir($cwd);

        $this->updateComposer($moduleConfig, 'predis/predis:~0.8.3');
    }

//    protected function updateComposer(BaseConfig $moduleConfig)
//    {
//        $beforeHash = hash_file('md5', 'composer.json');
//
//        if ($moduleConfig->isEnabled()) {
//            exec(
//                'php composer.phar require --no-update predis/predis:~0.8.3;' .
//                'php composer.phar require --no-update channelgrabber/predis:~1.0.1;'
//            );
//            $afterHash = hash_file('md5', 'composer.json');
//
//            if ($beforeHash != $afterHash) {
//                $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer..." . Console::COLOR_GREEN);
//                exec('php composer.phar update predis/predis channelgrabber/predis');
//            }
//        } else {
//            // remove
//            echo "IMPLEMENT REMOVE!\n";
//        }
//    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $nodeFile = Chef::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        if ($moduleConfig->isEnabled()) {
            $node->setKey('redis|enabled', true);
            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.redis.global.php',
                'config/autoload/di.redis.global.php'
            );
        } else {
            $node->removeKey('redis');
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.redis.global.php');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "SKELETON: Updated node ' . $config->getNode() . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }

    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(false);
    }
}