<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLogTable extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function change()
    {
        $this->table('stockAdjustmentLog', ['id' => false, 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string', ['null' => true])
            ->addColumn('date', 'date', ['null' => true])
            ->addColumn('time', 'time', ['null' => true])
            ->addColumn('stid', 'string', ['null' => true])
            ->addColumn('itid', 'string', ['null' => true])
            ->addColumn('action', 'string', ['null' => true])
            ->addColumn('organisationUnitId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('accountId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('listingId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('productId', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sku', 'string', ['null' => true])
            ->addColumn('stockManagement', 'boolean', ['null' => true])
            ->addColumn('type', 'string', ['null' => true])
            ->addColumn('operator', 'string', ['null' => true])
            ->addColumn('quantity', 'integer', ['null' => true])
            ->addColumn('applied', 'boolean', ['null' => true, 'default' => false])
            ->addIndex(['id'])
            ->addIndex(['organisationUnitId', 'sku'])
            ->addIndex(['sku'])
            ->addIndex(['stid'])
            ->addIndex(['date', 'time'])
            ->addIndex(['organisationUnitId', 'date'])
            ->addIndex(['accountId', 'date'])
            ->addIndex(['accountId', 'sku'])
            ->addIndex(['listingId'])
            ->addIndex(['productId'])
            ->addIndex(['quantity', 'organisationUnitId', 'sku'])
            ->addIndex(['type', 'operator'])
            ->addIndex(['action'])
            ->create();
    }
}
