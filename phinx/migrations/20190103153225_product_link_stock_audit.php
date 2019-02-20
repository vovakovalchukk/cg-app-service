<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class ProductLinkStockAudit extends AbstractOnlineSchemaChange
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('stockAdjustmentLog', implode(', ', [
            'ADD COLUMN `referenceSku` VARCHAR(255) NULL AFTER `sku`',
            'ADD COLUMN `referenceQuantity` INT(11) NULL AFTER `quantity`',
        ]), 200);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('stockAdjustmentLog', implode(', ', [
            'DROP COLUMN `referenceSku`',
            'DROP COLUMN `referenceQuantity`',
        ]), 200);
    }
}