<?php

use Phinx\Migration\AbstractMigration;

class PurchaseOrder extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('purchaseOrder');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('status', 'string')
            ->addColumn('externalId', 'integer')
            ->addColumn('created', 'datetime')
            ->addIndex('organisationUnitId')
            ->create();

        $table = $this->table('purchaseOrderItem');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('purchaseOrderId', 'integer')
            ->addColumn('sku', 'string')
            ->addColumn('quanitity', 'integer')
            ->addIndex('organisationUnitId')
            ->create();
    }
}
