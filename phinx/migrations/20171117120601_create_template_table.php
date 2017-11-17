<?php

use Phinx\Migration\AbstractMigration;

class CreateTemplateTable extends AbstractMigration
{
    public function change()
    {
        $this->table('userPreference')
            ->addColumn('type', 'string')
            ->addColumn('paperPage', 'string')
            ->addColumn('elements', 'string')
            ->addColumn('name', 'string')
            ->addColumn('typeId', 'string')
            ->addColumn('editable', 'boolean')
            ->addColumn('mongoId', 'string')
            ->addIndex(['mongoId'], ['unique' => true])
            ->create();
    }
}