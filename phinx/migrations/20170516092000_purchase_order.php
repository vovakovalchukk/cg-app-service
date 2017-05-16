<?php

use Phinx\Migration\AbstractMigration;

class PurchaseOrder extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('purchaseOrder');

        $table->addColumn('id', 'integer')
        ->addColumn('organisationUnitId', 'integer')
        ->addColumn('status', 'string')
        ->addColumn('externalId', 'integer')
        ->addColumn('created', 'datetime')
        ->addIndex('organisationUnitId')
        ->addIndex('id')
        ->create();

        $table = $this->table('purchaseOrderItem');



    }



}
