<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class CreateTemplateTable extends AbstractMigration
{
    public function change()
    {
        $this->table('template', ['collation' => 'utf8_general_ci'])
            ->addColumn('type', 'string')
            ->addColumn('paperPage', 'string')
            ->addColumn('elements', 'string', ['limit' => Adapter::TEXT_LONG])
            ->addColumn('name', 'string')
            ->addColumn('typeId', 'string')
            ->addColumn('editable', 'boolean')
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}