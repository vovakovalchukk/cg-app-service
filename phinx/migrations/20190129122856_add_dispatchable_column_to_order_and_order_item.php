<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddDispatchableColumnToOrderAndOrderItem extends AbstractOnlineSchemaChange
{

    public function up()
    {
        $alter = 'ADD dispatchable TINYINT(1) NOT NULL DEFAULT 0';
        $this->onlineSchemaChange('order', $alter);
        $this->onlineSchemaChange('item', $alter);
    }

    public function down()
    {
        $alter = 'drop dispatchable';
        $this->onlineSchemaChange('order', $alter);
        $this->onlineSchemaChange('item', $alter);
    }
}