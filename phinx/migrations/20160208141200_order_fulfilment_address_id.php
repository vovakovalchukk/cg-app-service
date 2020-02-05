<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderFulfilmentAddressId extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
