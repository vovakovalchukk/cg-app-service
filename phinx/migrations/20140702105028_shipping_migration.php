<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ShippingMigration extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $shippingMethodTable = $this->table('shippingMethod', ['collation' => 'utf8_general_ci']);
        $shippingMethodTable->addColumn('channel', 'string')
                           ->addColumn('method', 'string')
                           ->addIndex(['channel', 'method'], ['unique' => true])
                           ->create();

        $shippingMethodExposureTable = $this->table('shippingMethodExposure', ['id' => false, 'collation' => 'utf8_general_ci']);
        $shippingMethodExposureTable->addColumn('shippingMethodId', 'integer')
                                   ->addColumn('organisationUnitId', 'integer')
                                   ->addForeignKey('shippingMethodId', 'shippingMethod', 'id',
                                        ['delete' => 'NOACTION', 'update' => 'NOACTION'])
                                   ->addIndex(['shippingMethodId', 'organisationUnitId'], ['unique' => true])
                                   ->create();
    }
}
