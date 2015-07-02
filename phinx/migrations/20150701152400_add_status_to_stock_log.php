<?php
use Phinx\Migration\AbstractMigration;

class AddStatusToStockLog extends AbstractMigration
{
    public function up()
    {
        $this->table("stockAdjustmentLog")
            ->addColumn("itemStatus", ["null" => true])
            ->addIndex(["itemStatus"])
            ->update();
    }

    public function down()
    {
        $this->table("stockAdjustmentLog")
            ->removeColumn("itemStatus")
            ->update();
    }
}
