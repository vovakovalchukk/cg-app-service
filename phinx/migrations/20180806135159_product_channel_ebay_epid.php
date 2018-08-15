<?php

use Phinx\Migration\AbstractMigration;

class ProductChannelEbayEpid extends AbstractMigration
{
    public function change()
    {
        $this->table('productEbayEpid', ['id' => false, 'primary_key' => ['productId', 'marketplace']])
            ->addColumn('productId', 'integer')
            ->addColumn('marketplace', 'integer')
            ->addColumn('epid', 'integer')
            ->create();
    }
}
