<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderIossNumber extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected $tables = ['order', 'orderLive'];

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            $this->onlineSchemaChange($table, 'ADD COLUMN `iossNumber` VARCHAR(25) NULL');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            $this->onlineSchemaChange($table, 'DROP COLUMN `iossNumber`');
        }
    }
}
