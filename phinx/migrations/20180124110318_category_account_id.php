<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class CategoryAccountId extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange(
            'category',
            'ADD COLUMN `accountId` int(11) UNSIGNED NULL, ADD INDEX `accountId` (`accountId`)'
        );
    }

    public function down()
    {
        $this->onlineSchemaChange(
            'category',
            'DROP INDEX `accountId`, DROP COLUMN `accountId`'
        );
    }
}