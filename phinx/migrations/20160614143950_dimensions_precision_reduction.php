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
            ->changeColumn('width', 'decimal', ['precision' => 12, 'scale' => 3])
            ->changeColumn('height', 'decimal', ['precision' => 12, 'scale' => 3])
            ->changeColumn('length', 'decimal', ['precision' => 12, 'scale' => 3])
            ->update();

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE_MODIFY)
            ->changeColumn('width', 'decimal', ['precision' => 12, 'scale' => 6])
            ->changeColumn('height', 'decimal', ['precision' => 12, 'scale' => 6])
            ->changeColumn('length', 'decimal', ['precision' => 12, 'scale' => 6])
            ->update();
    }
}
