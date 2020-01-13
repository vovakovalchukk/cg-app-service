<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderAllTable extends AbstractMigration implements EnvironmentAwareInterface
{
    const OLD_TABLE_NAME = 'order';
    const TABLE_NAME = 'orderLive';
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'phinx:migrateMongoOrderDataToMysql';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->duplicateTable(static::OLD_TABLE_NAME, static::TABLE_NAME);
        $this->table(static::OLD_TABLE_NAME)
             ->addColumn('archived', 'boolean')
             ->update();
        $this->insertMongoData();
    }

    public function down()
    {
        $this->dropTable(static::TABLE_NAME);

        $this->table(static::OLD_TABLE_NAME)
             ->removeColumn('archived')
             ->update();
    }

    protected function duplicateTable($oldTableName, $newTableName)
    {
        $createTableSql = 'CREATE TABLE `' . $newTableName . '` LIKE `' . $oldTableName . '`;';
        $copyDataSql = 'INSERT `' . $newTableName . '` SELECT * FROM `' . $oldTableName . '`;';
        $this->execute($createTableSql);
        $this->execute($copyDataSql);
    }

    protected function insertMongoData()
    {
        if (file_exists(__DIR__ . static::CILEX_LOCATION)) {
            echo shell_exec('php ' . __DIR__ . static::CILEX_LOCATION . ' ' . static::CILEX_CMD);
        }
    }
}

