<?php
use Phinx\Migration\AbstractMigration;

class MigrateTradingCompanyInvoiceMappings extends AbstractMigration
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'phinx:migrateOUInvoiceMappings';

    /**
     * Migrate Up.
     */
    public function up()
    {
        if (!file_exists(__DIR__ . static::CILEX_LOCATION)) {
            return;
        }

        passthru('php ' . __DIR__ . static::CILEX_LOCATION . ' ' . static::CILEX_CMD, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException('Mongo migration failed!');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // No-op
    }
}
