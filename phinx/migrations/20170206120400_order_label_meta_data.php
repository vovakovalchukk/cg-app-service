<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderLabelMetaData extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('orderLabel')
            ->addColumn('organisationUnitId', 'integer')
            ->addColumn('orderId', 'string', ['limit' => 120])
            ->addColumn('status', 'string', ['limit' => 20])
            ->addColumn('created', 'datetime')
            ->addColumn('externalId', 'string', ['null' => true])
            ->addColumn('shippingAccountId', 'integer', ['null' => true])
            ->addColumn('shippingServiceCode', 'string', ['null' => true])
            ->addColumn('channelName', 'string', ['null' => true])
            ->addColumn('courierName', 'string', ['null' => true])
            ->addColumn('courierService', 'string', ['null' => true])
            ->addColumn('deliveryInstructions', 'text', ['null' => true])
            ->addColumn('signature', 'boolean', ['null' => true])
            ->addColumn('parcels', 'integer', ['null' => true])
            ->addColumn('insurance', 'boolean', ['null' => true])
            ->addColumn('insuranceMonetary', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addColumn('mongoId', 'string', ['limit' => 24, 'null' => true])
            ->addIndex('mongoId')
            ->addIndex('orderId')
            ->create();

        $this->table('orderLabelParcel')
            ->addColumn('orderLabelId', 'integer')
            ->addColumn('number', 'integer')
            ->addColumn('weight', 'decimal', ['precision' => 12, 'scale' => 3])
            ->addColumn('width', 'decimal', ['precision' => 12, 'scale' => 3])
            ->addColumn('height', 'decimal', ['precision' => 12, 'scale' => 3])
            ->addColumn('length', 'decimal', ['precision' => 12, 'scale' => 3])
            ->addIndex('orderLabelId')
            ->create();
    }
}
