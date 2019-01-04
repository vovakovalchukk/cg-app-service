<?php
use Phinx\Migration\AbstractMigration;

class ProductLinkStockAudit extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('stockAdjustmentLog')
            ->addColumn('referenceSku', 'string', ['after' => 'sku', 'null' => true])
            ->addColumn('referenceQuantity', 'integer', ['after' => 'quantity', 'null' => true])
            ->update();
    }
}