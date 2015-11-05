<?php
use Phinx\Migration\AbstractMigration;

class StockLogFractionalTimes extends AbstractMigration
{
    protected function getTableNames()
    {
        return ['stockLog', 'stockAdjustmentLog'];
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table('stockLog')->changeColumn('time', 'time', ['null' => true, 'length' => 6])->update();
        $this->table('stockAdjustmentLog')->changeColumn('time', 'time', ['null' => false, 'length' => 6])->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('stockLog')->changeColumn('time', 'time', ['null' => true])->update();
        $this->table('stockAdjustmentLog')->changeColumn('time', 'time', ['null' => false])->update();
    }
}
