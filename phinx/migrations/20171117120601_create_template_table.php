<?php
use Phinx\Db\Adapter\MysqlAdapter as Adapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class CreateTemplateTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('template', ['collation' => 'utf8_general_ci'])
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('type', 'string')
            ->addColumn('paperPage', 'string')
            ->addColumn('elements', 'string', ['limit' => Adapter::TEXT_LONG])
            ->addColumn('name', 'string')
            ->addColumn('typeId', 'string')
            ->addColumn('editable', 'boolean')
            ->addColumn('mongoId', 'string', ['null' => true])
            ->create();
    }
}