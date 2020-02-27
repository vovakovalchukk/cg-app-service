<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockLogTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('stockLog', ['id' => false, 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string', ['null' => true])
            ->addColumn('date', 'date', ['null' => true])
            ->addColumn('time', 'time', ['null' => true])
            ->addColumn('itid', 'string', ['null' => true])
            ->addColumn('organisationUnitId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('stockId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('locationId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('allocatedQty', 'integer', ['null' => true])
            ->addColumn('onHandQty', 'integer', ['null' => true])
            ->addIndex(['id'])
            ->addIndex(['organisationUnitId', 'sku'])
            ->addIndex(['date', 'time'])
            ->addIndex(['itid'])
            ->addIndex(['stockId'])
            ->addIndex(['locationId'])
            ->addIndex(['sku'])
            ->create();

        $this->table('stockLogToAdjustmentLogMap', ['id' => false, 'primary_key' => ['stockLogId', 'stockAdjustmentLogId'], 'collation' => 'utf8_general_ci'])
            ->addColumn('stockLogId', 'integer')
            ->addColumn('stockAdjustmentLogId', 'integer')
            ->addIndex(['stockLogId', 'stockAdjustmentLogId'])
            ->create();
    }
}
