<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockLogListingStatus extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
