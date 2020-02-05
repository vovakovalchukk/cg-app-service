<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductDetailSupplier extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `supplierId` INT(11)');
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `supplierId`');
    }
}