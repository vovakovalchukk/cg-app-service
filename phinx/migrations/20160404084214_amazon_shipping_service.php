<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonShippingService extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'amazon.shippingService';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this
            ->table(static::TABLE, ['id' => false, 'primary_key' => ['id'], 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string', ['null' => false])
            ->addColumn('region', 'string', ['null' => false])
            ->addColumn('carrier', 'string', ['null' => false])
            ->addColumn('service', 'string', ['null' => false])
            ->addColumn('currencyCode', 'string', ['null' => false, 'length' => 3])
            ->addColumn('rate', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 4])
            ->addColumn('deliveryExperience', 'string', ['null' => false])
            ->addColumn('carrierWillPickUp', 'boolean', ['null' => false])
            ->addIndex(['region', 'carrier', 'service'])
            ->addIndex(['currencyCode'])
            ->addIndex(['deliveryExperience'])
            ->addIndex(['carrierWillPickUp'])
            ->create();
    }
}
