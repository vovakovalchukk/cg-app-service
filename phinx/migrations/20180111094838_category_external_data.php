<?php

use Phinx\Migration\AbstractMigration;

class CategoryExternalData extends AbstractMigration
{
    const TABLE_NAME = 'categoryExternal';

    public function change()
    {
        $this->table(static::TABLE_NAME,  ['id' => false, 'primary_key' => 'categoryId'])
            ->addColumn('categoryId', 'integer', ['autoIncrement' => false, 'null' => false, 'signed' => false])
            ->addColumn('channel', 'string', ['null' => false])
            ->addIndex('categoryId')
            ->addForeignKey('categoryId', 'category', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
