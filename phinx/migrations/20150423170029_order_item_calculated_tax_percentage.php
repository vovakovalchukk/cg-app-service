<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderItemCalculatedTaxPercentage extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'item';
    const COLUMN_NAME = 'calculatedTaxPercentage';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
