<?php
use Phinx\Migration\AbstractMigration;

class ProductWeightReduceDp extends AbstractMigration
{
    const TABLE_MODIFY = 'productDetail';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->changeColumn('weight', 'decimal', ['precision' => 12, 'scale' => 3, 'null' => true])
            ->update();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->changeColumn('weight', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->update();
    }
}
