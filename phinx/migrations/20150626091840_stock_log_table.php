<?php
use Phinx\Migration\AbstractMigration;

class StockLogTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stockLog')
            ->addColumn('date', 'date', ['null' => true])
            ->addColumn('time', 'time', ['null' => true])
            ->addColumn('itid', 'integer', ['null' => true])
            ->addColumn('organisationUnitId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('stockId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('locationId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('allocatedQty', 'integer', ['null' => true])
            ->addColumn('onHandQty', 'integer', ['null' => true])
            ->addIndex(['organisationUnitId', 'sku'])
            ->create();
    }
}
