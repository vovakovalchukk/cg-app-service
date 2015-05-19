<?php
use Phinx\Migration\AbstractMigration;

class OrderItemCalculatedTaxPercentage extends AbstractMigration
{
    const TABLE_NAME = 'item';
    const COLUMN_NAME = 'calculatedTaxPercentage';

    public function up()
    {
        $this->table(static::TABLE_NAME)
             ->addColumn(static::COLUMN_NAME, 'decimal', ['precision' => 12, 'scale' => 4])
             ->update();
    }

    public function down()
    {
        $this->table(static::TABLE_NAME)
             ->removeColumn(static::COLUMN_NAME)
             ->update();
    }
}
