<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderStockAuditIndexes extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('order')
            ->addIndex('purchaseDate')
            ->update();

        $this->table('stockAudit')
            ->addIndex('stid')
            ->update();

        $this->table('stockAuditSku')
            ->addIndex('sku')
            ->update();
    }
}
