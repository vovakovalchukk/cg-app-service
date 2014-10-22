<?php

use Phinx\Migration\AbstractMigration;

class StockAuditTables extends AbstractMigration
{
    public function up()
    {
        $stockAudit = $this->table('stockAudit');
        $stockAudit
            ->addColumn('guid', 'string')
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
        $this->table('stockAudit')->drop();
        $this->table('stockAuditProductId')->drop();
        $this->table('stockAuditListingId')->drop();
    }
}
