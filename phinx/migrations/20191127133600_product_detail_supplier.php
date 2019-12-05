<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ProductDetailSupplier extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `supplierId` INT(11)');
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `supplierId`');
    }
}