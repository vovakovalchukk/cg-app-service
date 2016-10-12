<?php

use Phinx\Migration\AbstractMigration;

class OrderItemMigration extends AbstractMigration
{
    public function change()
    {
        $item = $this->table('item', ['id' => false, 'primary_key' => 'id', 'collation' => 'utf8_general_ci']);
        $item->addColumn('id', 'string')
            ->addColumn('orderId', 'string')
            ->create();
    }
}
