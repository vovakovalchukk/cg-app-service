<?php

use Phinx\Migration\AbstractMigration;

class OrderLink extends AbstractMigration
{
    public function change()
    {
        $this->table('orderLink')
            ->create();

        $this->table('orderLinkOrders')
            ->addColumn('orderLinkId', 'integer')
            ->addColumn('orderId', 'string', ['limit' => 120])
            ->addIndex('orderLinkId')
            ->addIndex('orderId')
            ->create();
    }
}