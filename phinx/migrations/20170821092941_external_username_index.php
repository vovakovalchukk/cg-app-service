<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class ExternalUsernameIndex extends AbstractOnlineSchemaChange
{
    const TABLE = 'order';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, "ADD INDEX `ExternalUsername` (`externalUsername`)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, "DROP INDEX `ExternalUsername`");
    }
}