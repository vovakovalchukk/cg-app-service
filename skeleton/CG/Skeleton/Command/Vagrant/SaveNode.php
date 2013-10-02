<?php
namespace CG\Skeleton\Command\Vagrant;

use CG\Skeleton\StartupCommand;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Chef\Role;
use CG\Skeleton\Chef\Node;

class SaveNode implements StartupCommand
{
    const ROLE = 'role';
    const ROLES = 'tools/chef/roles/';
    const NODES = 'tools/chef/nodes/';

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
        exec('git checkout ' . $config->getBranch() . ' > /dev/null');

        $chefConfig = $config->get('Chef', new ZendConfig($this->defaults, true));
        $this->saveRole($config, $chefConfig);
        $this->saveNode($config, $chefConfig);
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

    protected function saveNode(Config $config, ZendConfig $chefConfig)
    {
        $nodeFile = static::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        $this->addRoleToNode($node, $config, $chefConfig);
        $this->configureCapistranoOnNode($node, $config, $chefConfig);
        $this->configureSiteOnNode($node, $config, $chefConfig);

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "SKELETON: Updated node ' . $config->getNode() . '" --only -- ' . $nodeFile
        );
    }

    protected function addRoleToNode(Node $node, Config $config, ZendConfig $chefConfig)
    {
        $node->addToRunList('role[' . $chefConfig->get(static::ROLE) . ']');
    }

    protected function configureCapistranoOnNode(Node $node, Config $config, ZendConfig $chefConfig)
    {
        $node->setKey('cg.capistrano.' . $config->getAppName() . '.deploy_to', $config->getVmPath());
        $node->setKey('cg.capistrano.' . $config->getAppName() . '.shared_structure.config', 'config');
        $node->setKey('cg.capistrano.' . $config->getAppName() . '.shared_structure.config/autoload', 'config/autoload');
        $node->setKey('cg.capistrano.' . $config->getAppName() . '.symlinks.config/host.php', 'config/host.php');
    }

    protected function configureSiteOnNode(Node $node, Config $config, ZendConfig $chefConfig)
    {
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.docroot', $config->getVmPath());
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.webroot', 'public');
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.configroot', 'config');
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.dataroot', 'data');
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.datadiroot', 'data/di');
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.hostname', $config->getHostname());
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.enabled', true);
        $node->setKey('configure_sites.include_certificates_in_dataroot', true);
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.configautoloadroot', 'config/autoload');
        $node->setKey('configure_sites.sites.' . $config->getAppName() . '.certificateroot', 'data/certificates');
    }
}