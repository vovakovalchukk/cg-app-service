<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddDispatchableColumnToOrderItem extends AbstractOnlineSchemaChange
{

    public function up()
    {
        $alter = 'ADD COLUMN dispatchable TINYINT(1) NOT NULL DEFAULT 0';
        $this->onlineSchemaChange('item', $alter);
    }

    public function down()
    {
        $alter = 'DROP COLUMN dispatchable';
        $this->onlineSchemaChange('item', $alter);
    }
}