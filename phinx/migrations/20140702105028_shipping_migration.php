<?php

use Phinx\Migration\AbstractMigration;

class ShippingMigration extends AbstractMigration
{
    public function change()
    {
        $shippingMethodTable = $this->table('shippingMethod');
        $shippingMethodTable->addColumn('channel', 'string')
                           ->addColumn('method', 'string')
                           ->addIndex(['channel', 'method'], ['unique' => true])
                           ->create();

        $shippingMethodExposureTable = $this->table('shippingMethodExposure', ['id' => false]);
        $shippingMethodExposureTable->addColumn('shippingMethodId', 'integer')
                                   ->addColumn('organisationUnitId', 'integer')
                                   ->addForeignKey('shippingMethodId', 'shippingMethod', 'id',
                                        ['delete' => 'NOACTION', 'update' => 'NOACTION'])
                                   ->addIndex(['shippingMethodId', 'organisationUnitId'], ['unique' => true])
                                   ->create();
    }
}