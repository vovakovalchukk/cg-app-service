<?php

use Phinx\Migration\AbstractMigration;

class CategoryAccountId extends AbstractMigration
{
    public function change()
    {
        $this->table('category')
            ->addColumn('accountId', 'integer', ['signed' => false, 'null' => true])
            ->addIndex('accountId')
            ->update();
    }
}