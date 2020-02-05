<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLogArchive extends AbstractMigration implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    protected function init()
    {
        require_once './config/env.local.php';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        if (ENVIRONMENT !== 'live') {
            $this->execute('CREATE TABLE `stockAdjustmentLogArchive` LIKE `stockAdjustmentLog`');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if (ENVIRONMENT !== 'live') {
            $this->execute('DROP TABLE IF EXISTS `stockAdjustmentLogArchive`');
        }
    }
}