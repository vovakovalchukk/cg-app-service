<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLogRelated extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLES = [
        'stockAdjustmentLogRelated',
        'stockAdjustmentLogRelatedArchive'
    ];

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (static::TABLES as $table) {
            $this
                ->table($table, ['id' => false, 'collation' => 'utf8_general_ci'])
                ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('stockAdjustmentLogId', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('organisationUnitId', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('sku', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('quantity', 'integer', ['null' => false])
                ->addIndex(['id'])
                ->addIndex(['organisationUnitId', 'sku'])
                ->addIndex(['sku'])
                ->create();
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (static::TABLES as $table) {
            $this->table($table)->drop();
        }
    }
}
