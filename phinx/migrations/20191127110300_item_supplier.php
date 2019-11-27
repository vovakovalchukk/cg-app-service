<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class ItemSupplier extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('item', 'ADD COLUMN `supplierId` INT(11)');
    }

    public function down()
    {
        $this->onlineSchemaChange('item', 'DROP COLUMN `supplierId`');
    }
}