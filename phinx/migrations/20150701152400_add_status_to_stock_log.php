<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class AddStatusToStockLog extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->table("stockAdjustmentLog")
            ->addColumn("itemStatus", "string", ["null" => true])
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
