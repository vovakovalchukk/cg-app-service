<?php
use Phinx\Migration\AbstractMigration;

class OrderStockAuditIndexes extends AbstractMigration
{
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
