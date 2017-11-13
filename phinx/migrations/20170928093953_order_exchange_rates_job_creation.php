<?php
use Phinx\Migration\AbstractMigration;

class OrderExchangeRatesJobCreation extends AbstractMigration
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'ad-hoc:updateOrderExchangeRates --quiet';

    /**
     * Migrate Up.
     */
    public function up()
    {
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
        // NoOp - Can't remove jobs
    }
}