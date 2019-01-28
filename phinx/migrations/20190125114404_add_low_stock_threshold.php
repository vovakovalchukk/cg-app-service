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

        $stockAlter = array_merge($alter, [
            'ADD COLUMN `lowStockThresholdTriggered` TINYINT(1) NOT NULL DEFAULT FALSE',
            'ADD INDEX `LowStockThresholdTriggered` (`lowStockThresholdTriggered`)'
        ]);

        $this->onlineSchemaChange('productSettings', implode(', ', $alter), 200);
        $this->onlineSchemaChange('stock', implode(', ', $stockAlter), 200);
    }

    public function down()
    {
        $alter = [
            'DROP COLUMN `lowStockThresholdOn`',
            'DROP COLUMN `lowStockThresholdValue`'
        ];

        $stockAlter = array_merge($alter, [
            'DROP COLUMN `lowStockThresholdTriggered`',
            'DROP INDEX `LowStockThresholdTriggered`'
        ]);

        $this->onlineSchemaChange('productSettings', implode(', ', $alter), 200);
        $this->onlineSchemaChange('stock', implode(', ', $stockAlter), 200);
    }
}
