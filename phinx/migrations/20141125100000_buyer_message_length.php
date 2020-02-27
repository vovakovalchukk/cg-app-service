<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class BuyerMessageLength extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
 