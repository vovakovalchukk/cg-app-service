<?php
use Phinx\Migration\AbstractMigration;

class ShippingMethodLength extends AbstractMigration
{
    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('shippingMethod', 'text', ['length' => 100])
            ->update();
        $orderLiveTable = $this->table('orderLive');
        $orderLiveTable->changeColumn('shippingMethod', 'text', ['length' => 100])
            ->update();
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('shippingMethod', 'string', ['length' => 80])
            ->update();
        $orderLiveTable = $this->table('orderLive');
        $orderLiveTable->changeColumn('shippingMethod', 'string', ['length' => 80])
            ->update();
    }
}
 