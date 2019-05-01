<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ListingModifiedTime extends AbstractOnlineSchemaChange
{
    const TABLE = 'listing';

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN lastModified DATETIME');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN lastModified');
    }
}