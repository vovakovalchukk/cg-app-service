<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAuditIndex extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table("stockAudit")->addIndex("id")->save();
        $this->table("stockAuditAccountId")->addIndex("stockAuditId")->save();
        $this->table("stockAuditListingId")->addIndex("stockAuditId")->save();
        $this->table("stockAuditOrganisationUnitId")->addIndex("stockAuditId")->save();
        $this->table("stockAuditProductId")->addIndex("stockAuditId")->save();
        $this->table("stockAuditSku")->addIndex("stockAuditId")->save();
        $this->table("stockAuditStockManagement")->addIndex("stockAuditId")->save();
        $this->table("stockAuditUserId")->addIndex("stockAuditId")->save();
    }

    public function down()
    {
        $this->table("stockAudit")->removeIndex("id")->save();
        $this->table("stockAuditAccountId")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditListingId")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditOrganisationUnitId")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditProductId")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditSku")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditStockManagement")->removeIndex("stockAuditId")->save();
        $this->table("stockAuditUserId")->removeIndex("stockAuditId")->save();
    }
}
