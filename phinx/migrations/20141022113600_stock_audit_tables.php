<?php

use Phinx\Migration\AbstractMigration;

class StockAuditTables extends AbstractMigration
{
    public function up()
    {
        $stockAudit = $this->table('stockAudit');
        $stockAudit
            ->addColumn('stid', 'string')
            ->addColumn('action', 'string')
            ->addColumn('time', 'datetime')
            ->create();
        $stockAuditProductId = $this->table('stockAuditProductId');
        $stockAuditProductId
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('productId', 'integer')
            ->addForeignKey('stockAuditId', 'stockAudit')
            ->create();
        $stockAuditListingId = $this->table('stockAuditListingId');
        $stockAuditListingId
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('listingId', 'string')
            ->addForeignKey('stockAuditId', 'stockAudit')
            ->create();
    }

    public function down()
    {
        $this->table('stockAuditListingId')->drop();
        $this->table('stockAuditProductId')->drop();
        $this->table('stockAudit')->drop();
    }
}
