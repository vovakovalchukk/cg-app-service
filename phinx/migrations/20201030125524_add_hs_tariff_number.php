<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddHsTariffNumber extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `hsTariffNumber` VARCHAR(30) NULL', 200);
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `hsTariffNumber`');
    }
}
