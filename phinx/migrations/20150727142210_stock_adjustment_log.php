<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLog extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE = 'stockAdjustmentLog';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->table(static::TABLE)->drop();
        $this
            ->table(static::TABLE, ['id' => false, 'collation' => 'utf8_general_ci'])
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('date', 'date', ['null' => false])
            ->addColumn('time', 'time', ['null' => false])
            ->addColumn('stid', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('itid', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('action', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('organisationUnitId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('accountId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('listingId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('productId', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('sku', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('stockManagement', 'boolean', ['null' => false])
            ->addColumn('type', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('operator', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('quantity', 'integer', ['null' => false])
            ->addColumn('applied', 'boolean', ['null' => false])
            ->addColumn('itemStatus', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('listingStatus', 'string', ['limit' => 50, 'null' => false])
            ->addIndex(['date', 'time', 'stid', 'action', 'accountId', 'listingId', 'productId', 'sku', 'type', 'operator', 'itemStatus', 'listingStatus'], ['name' => 'AllColumns', 'unique' => true])
            ->addIndex(['id'])
            ->addIndex(['organisationUnitId', 'sku'])
            ->addIndex(['sku'])
            ->addIndex(['stid'])
            ->addIndex(['itid'])
            ->addIndex(['date', 'time'])
            ->addIndex(['organisationUnitId', 'date'])
            ->addIndex(['accountId', 'date'])
            ->addIndex(['accountId', 'sku'])
            ->addIndex(['listingId'])
            ->addIndex(['productId'])
            ->addIndex(['quantity', 'organisationUnitId', 'sku'])
            ->addIndex(['type', 'operator'])
            ->addIndex(['action'])
            ->addIndex(['itemStatus'])
            ->addIndex(['listingStatus'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE)
            ->removeIndex(['date', 'time', 'stid', 'action', 'accountId', 'listingId', 'productId', 'sku', 'type', 'operator', 'itemStatus', 'listingStatus'], ['name' => 'AllColumns', 'unique' => true])
            ->changeColumn('id', 'string', ['limit' => 255])
            ->changeColumn('date', 'date')
            ->changeColumn('stid', 'string', ['limit' => 255])
            ->changeColumn('itid', 'string', ['limit' => 255])
            ->changeColumn('action', 'string', ['limit' => 255])
            ->changeColumn('organisationUnitId', 'integer', ['signed' => false])
            ->changeColumn('accountId', 'integer', ['signed' => false])
            ->changeColumn('listingId', 'integer', ['signed' => false])
            ->changeColumn('productId', 'integer', ['signed' => false])
            ->changeColumn('sku', 'string', ['limit' => 255])
            ->changeColumn('stockManagement', 'boolean')
            ->changeColumn('type', 'string', ['limit' => 255])
            ->changeColumn('operator', 'string', ['limit' => 255])
            ->changeColumn('quantity', 'integer')
            ->changeColumn('applied', 'boolean')
            ->changeColumn('itemStatus', 'string', ['limit' => 255])
            ->changeColumn('listingStatus', 'string', ['limit' => 255])
            ->update();
    }
}
