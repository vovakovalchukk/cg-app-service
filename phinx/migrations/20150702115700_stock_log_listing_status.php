<?php
use Phinx\Migration\AbstractMigration;

class StockLogListingStatus extends AbstractMigration
{
    public function up()
    {
        $this->table("stockAdjustmentLog")
            ->addColumn("listingStatus", "string", ["null" => true])
            ->addIndex(["listingStatus"])
            ->update();
    }

    public function down()
    {
        $this->table("stockAdjustmentLog")
            ->removeColumn("listingStatus")
            ->update();
    }
}
