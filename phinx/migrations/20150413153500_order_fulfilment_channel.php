<?php
use Phinx\Migration\AbstractMigration;
use CG\Order\Shared\Entity as Order;

class OrderFulfilmentChannel extends AbstractMigration
{
    public function up()
    {
        foreach(["order", "orderLive"] as $table) {
            $this->table($table)
                ->addColumn('fulfilmentChannel', 'string', ['default' => Order::DEFAULT_FULFILMENT_CHANNEL])
                ->update();
        }
    }

    public function down()
    {
        foreach(["order", "orderLive"] as $table) {
            $this->table($table)
                ->removeColumn('fulfilmentChannel')
                ->update();
        }
    }
}
