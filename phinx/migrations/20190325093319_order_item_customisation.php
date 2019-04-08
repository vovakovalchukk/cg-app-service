<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderItemCustomisation extends AbstractOnlineSchemaChange
{
    const TABLE = 'item';

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN customisation MEDIUMTEXT');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN customisation');
    }
}