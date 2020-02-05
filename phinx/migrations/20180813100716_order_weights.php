<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderWeights extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLES = ['order', 'orderLive'];

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'ADD COLUMN `weight` DOUBLE(12, 5) DEFAULT NULL');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'DROP COLUMN `weight`');
        }
    }
}