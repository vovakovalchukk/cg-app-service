<?php
use Phinx\Migration\AbstractMigration;

class DimensionsPrecisionReduction extends AbstractMigration
{
    const TABLE_MODIFY = 'productDetail';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->changeColumn('width', 'decimal', ['precision' => 12, 'scale' => 3, 'null' => true])
            ->changeColumn('height', 'decimal', ['precision' => 12, 'scale' => 3, 'null' => true])
            ->changeColumn('length', 'decimal', ['precision' => 12, 'scale' => 3, 'null' => true])
            ->update();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->changeColumn('width', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->changeColumn('height', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->changeColumn('length', 'decimal', ['precision' => 12, 'scale' => 6, 'null' => true])
            ->update();
    }
}
