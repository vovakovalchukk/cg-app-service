<?php

use Phinx\Migration\AbstractMigration;

class ProductCategory extends AbstractMigration
{
    const TABLE_NAME = 'category';

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('externalId', 'string', ['length' => '155', 'null' => false])
            ->addColumn('parentId', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('title', 'string', ['null' => false])
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('channel', 'string', ['length' => '80', 'null' => false])
            ->addColumn('marketplace', 'string', ['length' => '20', 'null' => true])
            ->addColumn('listable', 'boolean', ['null' => true])
            ->addColumn('enabled', 'boolean', ['null' => true])
            ->addIndex('externalId')
            ->addIndex('parentId')
            ->addIndex('marketplace')
            ->addIndex('listable')
            ->addIndex('enabled')
            ->addIndex(['externalId', 'channel', 'marketplace'], ['unique' => true])
            ->create();
    }
}
