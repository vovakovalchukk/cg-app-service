<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class TemplateFavourite extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('template')
            ->addColumn('favourite', 'boolean', ['null' => true, 'default' => false])
            ->addIndex(['organisationUnitId', 'favourite'])
            ->update();
    }
}