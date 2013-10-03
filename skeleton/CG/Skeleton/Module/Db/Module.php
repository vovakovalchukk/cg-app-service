<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Node;
use DirectoryIterator;

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

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        if ($moduleConfig->isEnabled()) {
            return;
        }
        $moduleConfig->setEnabled(true);
        $this->configure($arguments, $config, $moduleConfig);
    }

    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->validateConfiguration($arguments, $config, $moduleConfig, true);
        $this->applyConfiguration($arguments, $config, $moduleConfig);
    }

    public function validateConfiguration(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $this->configureStorageNode($arguments, $config, $moduleConfig, $reconfigure);
        $this->configureDatabaseName($arguments, $config, $moduleConfig, $reconfigure);
    }

    public function configureStorageNode(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $storageNodes = array();
        for (
            $iterator = new DirectoryIterator($config->getInfrastructurePath() . '/tools/chef/data_bags/');
            $iterator->valid();
            $iterator->next()
        ) {
            $match = array();
            if ($iterator->isDir() && preg_match('/^storage_(?<node>.+)_users$/', $iterator->getBasename(), $match)) {
                $storageNodes[$match['node']] = $match['node'];
            }
        }

        $storageNode = $moduleConfig->getStorageNode();

        while ($reconfigure || (!$storageNode && !isset($storageNodes[$storageNode]))) {
            $reconfigure = false;

            $this->getConsole()->writeln('Available Storage Nodes:');
            foreach ($storageNodes as $node) {
                $this->getConsole()->writeln('   * ' . $node);
            }

            $storageNode = $this->getConsole()->ask(
                'Please specify the storage node you wish to connect to',
                $storageNode ?: $config->getNode()
            );

            if ($storageNode && !isset($storageNodes[$storageNode])) {
                $createStorageNode = $this->getConsole()->askWithOptions(
                    'Create new storage node \'' . $storageNode . '\'',
                    array('y', 'n'),
                    'y'
                );

                if ($createStorageNode == 'y') {
                    mkdir(
                        $config->getInfrastructurePath() . '/tools/chef/data_bags/storage_' . $storageNode ,
                        0700,
                        true
                    );

                    mkdir(
                        $config->getInfrastructurePath() . '/tools/chef/data_bags/storage_' . $storageNode . '_users',
                        0700,
                        true
                    );

                    $storageNodes[$storageNode] = $storageNode;
                } else {
                    $storageNode = '';
                }
            }
        }

        $moduleConfig->setStorageNode($storageNode);
    }

    public function configureDatabaseName(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $databaseName = $moduleConfig->getDatabaseName();
        while ($reconfigure || !$databaseName) {
            $reconfigure = false;
            $databaseName = $this->getConsole()->ask(
                'Please specify the database you wish to connect to',
                $databaseName ?: $config->getAppName()
            );
        }
        $moduleConfig->setDatabaseName($databaseName);
    }

    public function applyConfiguration(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->validateConfiguration($arguments, $config, $moduleConfig);

        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig);
        chdir($cwd);
    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $nodeFile = Chef::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        if ($moduleConfig->isEnabled()) {
            $node->setKey('database|enabled', true);
            $node->setKey('database|storage_choice', $moduleConfig->getStorageNode());
            $node->setKey('database|database_choice', $moduleConfig->getDatabaseName());

            $node->removeKey('database|users_choice');
            foreach ($moduleConfig->getDatabaseUsers() as $user) {
                $node->setKey('database|users_choice|' . $user, true);
            }

            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/database.local.php',
                'config/autoload/database.local.php'
            );
        } else {
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/database.local.php');
            $node->removeKey('database');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "SKELETON: Updated node ' . $config->getNode() . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }
}