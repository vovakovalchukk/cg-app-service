<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderFulfilmentAddressIndex extends AbstractOnlineSchemaChange
{
    protected const TABLES = ['order', 'orderLive'];

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'ADD INDEX `fulfilmentAddressId` (`fulfilmentAddressId`)');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'DROP INDEX `fulfilmentAddressId`');
        }
    }
}