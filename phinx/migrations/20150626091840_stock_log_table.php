<?php
use Phinx\Migration\AbstractMigration;

class StockLogTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stockLog')
            ->addColumn('date', 'date', ['null' => true])
            ->addColumn('time', 'time', ['null' => true])
            ->addColumn('itid', 'string', ['null' => true])
            ->addColumn('organisationUnitId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('stockId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('locationId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('allocatedQty', 'integer', ['null' => true])
            ->addColumn('onHandQty', 'integer', ['null' => true])
            ->addIndex(['organisationUnitId', 'sku'])
            ->addIndex(['date', 'time'])
            ->addIndex(['itid'])
            ->addIndex(['stockId'])
            ->addIndex(['locationId'])
            ->addIndex(['sku'])
            ->create();

        $this->table('stockLogToAdjustmentLogMap', ['id' => false, 'primary_key' => ['stockLogId', 'stockAdjustmentLogId']])
            ->addColumn('stockLogId', 'integer')
            ->addColumn('stockAdjustmentLogId', 'integer')
            ->addIndex(['stockLogId', 'stockAdjustmentLogId'])
            ->create();
    }
}
