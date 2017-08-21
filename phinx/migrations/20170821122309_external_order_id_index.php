<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ExternalOrderIdIndex extends AbstractOnlineSchemaChange
{
    const TABLE_ORDER = 'order';
    const TABLE_ORDER_LIVE = 'orderLive';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "ADD INDEX `externalId` (externalId), ADD INDEX `channel` (channel, externalId)");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "ADD INDEX `externalId` (externalId), ADD INDEX `channel` (channel, externalId)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "DROP INDEX `externalId`, DROP INDEX `channel`");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "DROP INDEX `externalId`, DROP INDEX `channel`");
    }
}
