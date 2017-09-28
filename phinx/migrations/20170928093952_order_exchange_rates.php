<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderExchangeRates extends AbstractOnlineSchemaChange
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'ad-hoc:updateOrderExchangeRates --quiet';

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

        passthru('php ' . __DIR__ . static::CILEX_LOCATION . ' ' . static::CILEX_CMD, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException('Failed to generate jobs to update orders exchange rates');
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