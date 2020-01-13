<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductWeightReduceDp extends AbstractMigration implements EnvironmentAwareInterface
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
