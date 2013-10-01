<?php
namespace CG\Skeleton\Command\Vagrant;

use CG\Skeleton\Command;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Chef\Role;

class SaveNode implements Command
{
    const ROLE = 'role';
    const ROLES = 'tools/chef/roles/';

    protected $defaults;

    public function __construct()
    {
        $this->defaults = array();
    }

    public function getName()
    {
        return 'Save Node Data';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath());
        exec('git checkout ' . $config->getBranch());

        $chefConfig = $config->get('Chef', new ZendConfig($this->defaults, true));
        $this->saveRole($config, $chefConfig);
        $config->offsetSet('Chef', $chefConfig);

        chdir($cwd);
        return true;
    }

    protected function saveRole(Config $config, ZendConfig $chefConfig)
    {
        $roleName = $chefConfig->get(static::ROLE);
        if (!$roleName) {
            $roleName = $config->getAppName();
            $chefConfig->offsetSet(static::ROLE, $roleName);
        }

        $roleFile = static::ROLES . $roleName . '.json';
        $role = new Role($roleFile);

        $role->addToRunList('role[apt]')
             ->addToRunList('role[cg]')
             ->addToRunList('role[percona]')
             ->addToRunList('role[web]');

        $role->save();

        exec(
            'git add ' . $roleFile . ';'
            . ' git commit -m "SKELETON: Updated role ' . $roleName . '" --only -- ' . $roleFile
        );
    }
}