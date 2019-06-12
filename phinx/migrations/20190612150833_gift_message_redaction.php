<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class GiftMessageRedaction extends AbstractOnlineSchemaChange
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('giftWrap', 'ADD COLUMN `giftWrapRedacted` BOOLEAN NOT NULL DEFAULT FALSE AFTER `orderItemId`');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('giftWrap', 'DROP COLUMN `giftWrapRedacted`');
    }
}