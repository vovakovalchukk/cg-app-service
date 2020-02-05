<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class MoreStockAuditInfo extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table('stockAuditSku', ['collation' => 'utf8_general_ci'])
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('sku', 'string')
            ->create();
        $this->table('stockAuditOrganisationUnitId', ['collation' => 'utf8_general_ci'])
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('organisationUnitId', 'integer')
            ->create();
        $this->table('stockAuditUserId', ['collation' => 'utf8_general_ci'])
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('userId', 'integer')
            ->create();
        $this->table('stockAuditAccountId', ['collation' => 'utf8_general_ci'])
            ->addColumn('stockAuditId', 'integer')
            ->addColumn('accountId', 'integer')
            ->create();
        $this->table('stockAuditStockManagement', ['collation' => 'utf8_general_ci'])
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
