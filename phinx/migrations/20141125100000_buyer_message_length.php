<?php
use Phinx\Migration\AbstractMigration;

class BuyerMessageLength extends AbstractMigration
{
    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('buyerMessage', 'text', ['null' => false])
                   ->update();
        $orderLiveTable = $this->table('orderLive');
        $orderLiveTable->changeColumn('buyerMessage', 'text', ['null' => false])
                       ->update();
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('buyerMessage', 'string', ['null' => false])
                   ->update();
        $orderLiveTable = $this->table('orderLive');
        $orderLiveTable->changeColumn('buyerMessage', 'string', ['null' => false])
                       ->update();
    }
}
 