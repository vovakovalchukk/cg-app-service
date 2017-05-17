<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Table\ForeignKey;

class PurchaseOrder extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('purchaseOrder');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('status', 'string')
            ->addColumn('externalId', 'string')
            ->addColumn('created', 'datetime')
            ->addIndex('organisationUnitId')
            ->create();

        $table = $this->table('purchaseOrderItem');

        $table->addColumn('organisationUnitId', 'integer')
            ->addColumn('purchaseOrderId', 'integer')
            ->addColumn('sku', 'string')
            ->addColumn('quantity', 'integer')
            ->addIndex('organisationUnitId')
            ->addForeignKey('purchaseOrderId', 'purchaseOrder', 'id', ['update' => ForeignKey::CASCADE, 'delete' => ForeignKey::CASCADE])
            ->create();
    }
}
