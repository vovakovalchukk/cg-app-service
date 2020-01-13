<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddLowStockThreshold extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $productSettingsAlter = [
            'ADD COLUMN `lowStockThresholdOn` TINYINT(1) NOT NULL DEFAULT FALSE',
            'ADD COLUMN `lowStockThresholdValue` INT(10) NULL DEFAULT NULL'
        ];

        $stockAlter = [
            'ADD COLUMN `lowStockThresholdOn` VARCHAR(20) NOT NULL DEFAULT \'default\'',
            'ADD COLUMN `lowStockThresholdValue` INT(10) NULL DEFAULT NULL',
            'ADD COLUMN `lowStockThresholdTriggered` TINYINT(1) NOT NULL DEFAULT FALSE',
            'ADD INDEX `LowStockThresholdTriggered` (`lowStockThresholdTriggered`)'
        ];

        $this->onlineSchemaChange('productSettings', implode(', ', $productSettingsAlter), 200);
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
