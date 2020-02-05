<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderExchangeRatesJobCreation extends AbstractMigration implements EnvironmentAwareInterface
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'ad-hoc:updateOrderExchangeRates --quiet';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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