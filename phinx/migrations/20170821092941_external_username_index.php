<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class ExternalUsernameIndex extends AbstractOnlineSchemaChange
{
    const TABLE_ORDER = 'order';
    const TABLE_ORDER_LIVE = 'orderLive';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "ADD INDEX `ExternalUsername` (`externalUsername`)");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "ADD INDEX `ExternalUsername` (`externalUsername`)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "DROP INDEX `ExternalUsername`");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "DROP INDEX `ExternalUsername`");
    }
}