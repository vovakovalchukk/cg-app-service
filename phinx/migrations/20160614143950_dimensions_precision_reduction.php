<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class DimensionsPrecisionReduction extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_MODIFY = 'productDetail';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
