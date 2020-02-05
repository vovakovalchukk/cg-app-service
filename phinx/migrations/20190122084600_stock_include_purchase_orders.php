<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class StockIncludePurchaseOrders extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $alter = 'ADD COLUMN `includePurchaseOrders` TINYINT(1) NOT NULL DEFAULT 0, '
            . 'ADD COLUMN `includePurchaseOrdersUseDefault` TINYINT(1) NOT NULL DEFAULT 1';
        $this->onlineSchemaChange('stock', $alter);
    }

    public function down()
    {
        $alter = 'DROP COLUMN `includePurchaseOrders`, '
            . 'DROP COLUMN `includePurchaseOrdersUseDefault`';
        $this->onlineSchemaChange('stock', $alter);
    }
}
