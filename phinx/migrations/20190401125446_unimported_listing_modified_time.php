<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class UnimportedListingModifiedTime extends AbstractOnlineSchemaChange
{
    const TABLE = 'unimportedListing';

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN lastModified DATETIME');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN lastModified');
    }
}