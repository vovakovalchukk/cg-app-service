<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class StockLocationOnPurchaseOrder extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $alter = 'DROP INDEX `StockIdLocationId`,'
            . 'ADD UNIQUE KEY `StockIdLocationId` (`stockId`,`locationId`),'
            . 'ADD COLUMN `onPurchaseOrder` INT(11) NOT NULL DEFAULT 0';
        $this->onlineSchemaChange('stockLocation', $alter);
    }

    public function down()
    {
        // We can't undo the unique index via online schema change as that requires a unique index
        $alter = 'DROP COLUMN `onPurchaseOrder`';
        $this->onlineSchemaChange('stockLocation', $alter);
    }

    protected function getAdditionalArguments(): array
    {
        // Note: this flag is dangerous. It will silently drop any duplicates.
        // We have checked the table for this particular change and believe it is fine.
        return ['--no-check-unique-key-change'];
    }
}
