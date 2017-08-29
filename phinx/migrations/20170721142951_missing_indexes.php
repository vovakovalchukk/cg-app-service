<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class MissingIndexes extends AbstractOnlineSchemaChange
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('shippingMethod', 'ADD INDEX `method` (`method`)');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('shippingMethod', 'DROP INDEX `method`');
    }
}