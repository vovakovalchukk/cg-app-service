<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLogRelated extends AbstractMigration implements EnvironmentAwareInterface
{
    protected const TABLE_STOCK_ADJUSTMENT_LOG_RELATED = 'stockAdjustmentLogRelated';
    protected const TABLE_STOCK_ADJUSTMENT_LOG_RELATED_ARCHIVE = 'stockAdjustmentLogRelatedArchive';
    protected const TABLES = [
        self::TABLE_STOCK_ADJUSTMENT_LOG_RELATED,
        self::TABLE_STOCK_ADJUSTMENT_LOG_RELATED_ARCHIVE,
    ];
    protected const TABLES_ENVIRONMENT_OVERRIDES = [
        self::TABLE_STOCK_ADJUSTMENT_LOG_RELATED_ARCHIVE =>
            [
                'dev' => true,
                'qa' => true,
                'live' => false,
            ],
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

    protected function getTableNames(): \Generator
    {
        foreach (static::TABLES as $table) {
            if (!$this->supportsProductionEnvironment($table)) {
                continue;
            }
            yield $table;
        }
    }

    protected function supportsProductionEnvironment(string $tableName): bool
    {
        if (!isset(static::TABLES_ENVIRONMENT_OVERRIDES[$tableName])) {
            return true;
        }
        if (!isset(static::TABLES_ENVIRONMENT_OVERRIDES[$tableName][ENVIRONMENT])) {
            return true;
        }
        return static::TABLES_ENVIRONMENT_OVERRIDES[$tableName][ENVIRONMENT];
    }
}
