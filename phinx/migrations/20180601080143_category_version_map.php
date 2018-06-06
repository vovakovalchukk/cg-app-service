<?php

use Phinx\Migration\AbstractMigration;

class CategoryVersionMap extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('categoryVersionMap')
            ->create();

        $this
            ->table('categoryVersionMapChannel')
            ->addColumn('categoryVersionMapId', 'integer')
            ->addColumn('channel', 'string', ['length' => '80', 'null' => false])
            ->addColumn('marketplace', 'string', ['length' => '20',])
            ->addColumn('accountId', 'integer', ['length' => '11'])
            ->addColumn('version', 'integer', ['null' => false])
            ->addForeignKey('categoryVersionMapId', 'categoryVersionMap', 'id',
                ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['channel', 'marketplace', 'accountId'], ['unique' => false])
            ->create();
    }
}