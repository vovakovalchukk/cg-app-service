<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderWeights extends AbstractOnlineSchemaChange
{
    protected const TABLES = ['order', 'orderLive'];

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