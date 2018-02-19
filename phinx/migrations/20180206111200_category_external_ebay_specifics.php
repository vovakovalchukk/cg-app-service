<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class CategoryExternalEbaySpecifics extends AbstractMigration
{
    public function change()
    {
        $this->table('ebayCategorySpecifics', ['collation' => 'utf8_general_ci', 'id' => false, 'primary_key' => 'categoryId'])
            ->addColumn('categoryId', 'integer', ['signed' => false])
            ->addColumn('specifics', 'string', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->create();
    }
}