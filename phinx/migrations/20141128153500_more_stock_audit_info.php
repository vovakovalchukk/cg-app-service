<?php
use Phinx\Migration\AbstractMigration;

class MoreStockAuditInfo extends AbstractMigration
{
    public function up()
    {
        $this->table('stockAuditSku')
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('sku', 'string')
            ->create();
        $this->table('stockAuditOrganisationUnitId')
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('organisationUnitId', 'integer')
            ->create();
        $this->table('stockAuditUserId')
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('userId', 'integer')
            ->create();
        $this->table('stockAuditAccountId')
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('accountId', 'integer')
            ->create();
        $this->table('stockAuditStockManagement')
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('stockManagement', 'boolean')
            ->create();
    }

    public function down()
    {
        $this->table('stockAuditSku')->drop();
        $this->table('stockAuditOrganisationUnitId')->drop();
        $this->table('stockAuditUserId')->drop();
        $this->table('stockAuditAccountId')->drop();
        $this->table('stockAuditStockManagement')->drop();
    }
}
