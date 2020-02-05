<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAuditTables extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $stockAudit = $this->table('stockAudit', ['collation' => 'utf8_general_ci']);
        $stockAudit
            ->addColumn('stid', 'string')
            ->addColumn('action', 'string')
            ->addColumn('time', 'datetime')
            ->create();
        $stockAuditProductId = $this->table('stockAuditProductId', ['collation' => 'utf8_general_ci']);
        $stockAuditProductId
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('productId', 'integer')
            ->addForeignKey('stockAuditId', 'stockAudit')
            ->create();
        $stockAuditListingId = $this->table('stockAuditListingId', ['collation' => 'utf8_general_ci']);
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
