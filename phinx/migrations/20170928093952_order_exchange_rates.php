<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderExchangeRates extends AbstractOnlineSchemaChange
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this->onlineSchemaChange(
                $table,
                'ADD COLUMN `exchangeRate` DECIMAL(12, 4), ADD COLUMN `exchangeRateCurrencyCode` VARCHAR(255)'
            );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (['order', 'orderLive'] as $table) {
            $this->onlineSchemaChange(
                $table,
                'DROP COLUMN `exchangeRate`, DROP COLUMN `exchangeRateCurrencyCode`'
            );
        }
    }
}