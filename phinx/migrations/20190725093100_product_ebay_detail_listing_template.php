<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class ProductEbayDetailListingTemplate extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('productEbayDetail')
            ->addColumn('listingTemplateId', 'integer', ['null' => true])
            ->update();
    }
}