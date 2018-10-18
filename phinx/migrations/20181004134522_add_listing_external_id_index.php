<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class AddListingExternalIdIndex extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange(
            'listing',
            'ADD INDEX externalId (externalId)'
        );
    }

    public function down()
    {
        $this->onlineSchemaChange(
            'listing',
            'DROP INDEX `externalId`'
        );
    }
}
