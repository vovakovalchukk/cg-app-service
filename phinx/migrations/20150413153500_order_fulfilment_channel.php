<?php
use Phinx\Migration\AbstractMigration;

class OrderFulfilmentChannel extends AbstractMigration
{
    public function up()
    {
        foreach(["order", "orderLive"] as $table) {
            $this->table($table)
                ->addColumn('fulfilmentChannel', 'string', ['default' => 'Merchant'])
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
