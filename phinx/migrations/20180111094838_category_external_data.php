<?php

use Phinx\Migration\AbstractMigration;

class CategoryExternalData extends AbstractMigration
{
    const TABLE_NAME = 'categoryExternal';

    public function change()
    {
        $this->table(static::TABLE_NAME,  ['id' => false, 'primary_key' => 'categoryId', 'collation' => 'utf8_general_ci'])
            ->addColumn('categoryId', 'integer', ['autoIncrement' => false, 'null' => false, 'signed' => false])
            ->addColumn('channel', 'string', ['limit' => 255, 'null' => false])
            ->addIndex('channel')
            ->create();
    }
}
