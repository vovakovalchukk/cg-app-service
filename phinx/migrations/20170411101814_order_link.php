<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderLink extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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