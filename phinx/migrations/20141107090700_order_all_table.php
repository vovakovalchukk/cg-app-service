<?php

use Phinx\Migration\AbstractMigration;

class OrderAllTable extends AbstractMigration
{
    const OLD_TABLE_NAME = 'order';
    const TABLE_NAME = 'orderLive';

    public function up()
    {
        $this->duplicateTableStructure(static::OLD_TABLE_NAME, static::TABLE_NAME);
        $table = $this->table(static::OLD_TABLE_NAME);
        $table->addColumn('archived', 'boolean')
              ->update();
    }

    public function down()
    {
        $this->dropTable(static::TABLE_NAME);

        $table = $this->table(static::OLD_TABLE_NAME);
        $table->removeColumn('archived')
              ->update();
    }

    protected function duplicateTableStructure($oldTableName, $newTableName)
    {
        $createTableSql = 'CREATE TABLE `' . (string) $newTableName . '` LIKE `' . (string) $oldTableName . '`;';
        $this->execute($createTableSql);
    }
}

