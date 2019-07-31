<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderLinkable extends AbstractOnlineSchemaChange
{
    protected $tables = ['order', 'orderLive'];

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