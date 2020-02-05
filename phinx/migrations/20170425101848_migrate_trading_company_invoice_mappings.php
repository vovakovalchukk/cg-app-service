<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class MigrateTradingCompanyInvoiceMappings extends AbstractMigration implements EnvironmentAwareInterface
{
    const CILEX_LOCATION = '/../../console/app.php';
    const CILEX_CMD = 'phinx:migrateOUInvoiceMappings';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

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
