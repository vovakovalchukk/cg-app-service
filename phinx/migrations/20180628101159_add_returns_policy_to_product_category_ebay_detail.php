<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AddReturnsPolicyToProductCategoryEbayDetail extends AbstractMigration
{
    public function change()
    {
        $this->table('productCategoryEbayDetail')
            ->addColumn('returnPolicy', 'string', ['limit' => MysqlAdapter::TEXT_TINY, 'null' => true])
            ->save();
    }
}