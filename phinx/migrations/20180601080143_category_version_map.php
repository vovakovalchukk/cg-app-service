<?php

use Phinx\Migration\AbstractMigration;

class CategoryVersionMap extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
     */
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
            ->addIndex(['channel', 'marketplace', 'accountId'], ['unique' => true])
            ->create();
    }
}