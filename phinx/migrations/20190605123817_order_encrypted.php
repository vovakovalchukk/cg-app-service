<?php
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderEncrypted extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table('orderEncrypted', ['id' => false, 'primary_key' => ['orderId']])
            ->addColumn('orderId', 'string', ['null' => false, 'limit' => 120])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('billingAddress', 'string', ['null' => true, 'length' => MysqlAdapter::TEXT_LONG])
            ->addColumn('shippingAddress', 'string', ['null' => true, 'length' => MysqlAdapter::TEXT_LONG])
            ->addColumn('fulfilmentAddress', 'string', ['null' => true, 'length' => MysqlAdapter::TEXT_LONG])
            ->addIndex(['organisationUnitId'])
            ->create();
    }
}