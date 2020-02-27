<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class StockLocationOnPurchaseOrder extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $alter = 'DROP INDEX `stockId`,'
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
