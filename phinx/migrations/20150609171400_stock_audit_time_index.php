<?php
use Phinx\Migration\AbstractMigration;

class StockAuditTimeIndex extends AbstractMigration
{
    public function change()
    {
        $this->table('stockAudit')
            ->addIndex('time')
            ->update();
    }
}
