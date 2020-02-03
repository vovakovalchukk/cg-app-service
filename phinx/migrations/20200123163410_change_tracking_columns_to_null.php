<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ChangeTrackingColumnsToNull extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('tracking', 'MODIFY `number` VARCHAR(120) DEFAULT NULL');
        $this->onlineSchemaChange('tracking', 'MODIFY `carrier` VARCHAR(120) DEFAULT NULL');
    }

    public function down()
    {
        $this->onlineSchemaChange('tracking', 'MODIFY `number` VARCHAR(120) DEFAULT NOT NULL');
        $this->onlineSchemaChange('tracking', 'MODIFY `carrier` VARCHAR(120) DEFAULT NOT NULL');
    }
}
