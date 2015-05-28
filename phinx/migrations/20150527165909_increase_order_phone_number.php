<?php

use Phinx\Migration\AbstractMigration;

class IncreaseOrderPhoneNumber extends AbstractMigration
{
    const TABLE_NAME = 'address';
    const COLUMN_NAME = 'phoneNumber';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this
            ->table(static::TABLE_NAME)
            ->changeColumn(static::COLUMN_NAME, 'string', ['limit' => 40, 'null' => true])
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
