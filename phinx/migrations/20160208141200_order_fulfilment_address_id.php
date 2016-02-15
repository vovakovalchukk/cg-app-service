<?php
use Phinx\Migration\AbstractMigration;

class OrderFulfilmentAddressId extends AbstractMigration
{
    public function change()
    {
        $this->table('order')
            ->addColumn('fulfilmentAddressId', 'string', ['null' => true])
            ->update();

        $this->table('orderLive')
            ->addColumn('fulfilmentAddressId', 'string', ['null' => true])
            ->update();
    }
}
