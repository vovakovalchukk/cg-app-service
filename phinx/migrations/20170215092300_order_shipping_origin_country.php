<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderShippingOriginCountry extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('order', 'ADD COLUMN `shippingOriginCountryCode` varchar(2) NULL');
        $this->onlineSchemaChange('orderLive', 'ADD COLUMN `shippingOriginCountryCode` varchar(2) NULL');
        $this->insertData('order');
        $this->insertData('orderLive');
    }

    public function down()
    {
        $this->onlineSchemaChange('order', 'DROP COLUMN `shippingOriginCountryCode`');
        $this->onlineSchemaChange('orderLive', 'DROP COLUMN `shippingOriginCountryCode`');
    }

    protected function insertData($table)
    {
        $query = 'UPDATE `cg_app`.`'.$table.'` AS `order`'
            . 'JOIN `directory`.`organisationUnit` AS `ou` ON (`order`.`organisationUnitId` = `ou`.`id`) '
            . 'SET `order`.`shippingOriginCountryCode` = `ou`.`addressCountryCode`';
        $this->execute($query);
    }
}