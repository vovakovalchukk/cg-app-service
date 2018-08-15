<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class UpdateProductDetailUpc extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY `upc` VARCHAR(13)', 200);
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'MODIFY `upc` VARCHAR(12)', 200);
    }
}