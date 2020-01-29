<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddReorderQuantity extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $productSettingsAlter = 'ADD COLUMN `reorderQuantity` INT(10) NULL DEFAULT 1';
        $stockAlter = 'ADD COLUMN `reorderQuantity` INT(10) NULL DEFAULT NULL';

        $this->onlineSchemaChange('productSettings', $productSettingsAlter, 200);
        $this->onlineSchemaChange('stock', $stockAlter, 200);
    }

    public function down()
    {
        $alter = 'DROP COLUMN `reorderQuantity`';

        $this->onlineSchemaChange('productSettings', $alter, 200);
        $this->onlineSchemaChange('stock', $alter, 200);
    }
}
