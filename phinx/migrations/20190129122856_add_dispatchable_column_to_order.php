<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class AddDispatchableColumnToOrder extends AbstractOnlineSchemaChange
{

    public function up()
    {
        $alter = 'ADD COLUMN dispatchable TINYINT(1) NOT NULL DEFAULT 0';
        $this->onlineSchemaChange('order', $alter);
    }

    public function down()
    {
        $alter = 'DROP COLUMN dispatchable';
        $this->onlineSchemaChange('order', $alter);
    }
}