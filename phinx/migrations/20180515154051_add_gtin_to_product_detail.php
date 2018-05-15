<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddGtinToProductDetail extends AbstractOnlineSchemaChange
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `gtin` VARCHAR(14) NULL', 200);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `gtin`');
    }
}