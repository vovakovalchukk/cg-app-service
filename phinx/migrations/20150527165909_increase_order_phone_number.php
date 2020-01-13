<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class IncreaseOrderPhoneNumber extends AbstractMigration implements EnvironmentAwareInterface
{
    const TABLE_NAME = 'address';
    const COLUMN_NAME = 'phoneNumber';

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
            ->table(static::TABLE_NAME)
            ->changeColumn(static::COLUMN_NAME, 'string', ['limit' => 255, 'null' => true])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this
            ->table(static::TABLE_NAME)
            ->changeColumn(static::COLUMN_NAME, 'string', ['limit' => 20, 'null' => true])
            ->update();
    }
}
