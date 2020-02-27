<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ItemSupplier extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('item', 'ADD COLUMN `supplierId` INT(11)');
    }

    public function down()
    {
        $this->onlineSchemaChange('item', 'DROP COLUMN `supplierId`');
    }
}