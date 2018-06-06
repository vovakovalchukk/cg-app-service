<?php

use Phinx\Migration\AbstractMigration;

class CategoryVersionMap extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('categoryVersionMap', ['id' => true, 'primary_key' => ['id']])
            ->create();

        $this
            ->table('categoryVersionMapChannel', ['id' => true, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('categoryVersionMapId', 'integer')
            ->addColumn('channel', 'string', ['null' => false])
            ->addColumn('marketplace', 'string')
            ->addColumn('accountId', 'integer')
            ->addColumn('version', 'integer', ['null' => false])
            ->addForeignKey('categoryVersionMapId', 'categoryVersionMap', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['channel', 'marketplace', 'accountId'], ['unique' => false])
            ->create();
    }
}