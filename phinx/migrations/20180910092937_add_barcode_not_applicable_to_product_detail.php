<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddBarcodeNotApplicableToProductDetail extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `barcodeNotApplicable` TINYINT(1) NULL', 200);
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `barcodeNotApplicable`');
    }
}