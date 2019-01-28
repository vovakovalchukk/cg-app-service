<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddLowStockThreshold extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $alter = [
            'ADD COLUMN `lowStockThresholdOn` TINYINT(1) NULL',
            'ADD COLUMN `lowStockThresholdValue` INT(10) NULL'
        ];

        $this->onlineSchemaChange('productSettings', implode(', ', $alter), 200);
        $this->onlineSchemaChange('stock', implode(', ', $alter), 200);
    }

    public function down()
    {
        $alter = [
            'DROP COLUMN `lowStockThresholdOn`',
            'DROP COLUMN `lowStockThresholdValue`'
        ];

        $this->onlineSchemaChange('productSettings', implode(', ', $alter), 200);
        $this->onlineSchemaChange('stock', implode(', ', $alter), 200);
    }
}
