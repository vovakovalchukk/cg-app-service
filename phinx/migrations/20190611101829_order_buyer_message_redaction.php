<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderBuyerMessageRedaction extends AbstractOnlineSchemaChange
{
    protected const TABLES = ['order', 'orderLive'];

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('orderEncrypted', 'ADD COLUMN `buyerMessage` LONGTEXT');
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'ADD COLUMN `buyerMessageRedacted` BOOLEAN DEFAULT FALSE AFTER `buyerMessage`');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'DROP COLUMN `buyerMessageRedacted`');
        }
        $this->onlineSchemaChange('orderEncrypted', 'DROP COLUMN `buyerMessage`');
    }
}