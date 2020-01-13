<?php
use CG\Order\Shared\Entity as Order;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderFulfilmentChannel extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
