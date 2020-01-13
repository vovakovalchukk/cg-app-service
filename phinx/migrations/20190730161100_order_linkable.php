<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderLinkable extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
            $this->onlineSchemaChange($table, 'ADD COLUMN `linkable` TINYINT(1) NOT NULL DEFAULT 1');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            $this->onlineSchemaChange($table, 'DROP COLUMN `linkable`');
        }
    }
}