<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockLogFractionalTimes extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
