<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class AmazonAccountDetailFulfilmentLatency extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('productAccountDetail', 'ADD COLUMN externalType VARCHAR(80), ADD COLUMN externalData JSON');
    }

    public function down()
    {
        $this->onlineSchemaChange('productAccountDetail', 'DROP COLUMN externalType, DROP COLUMN externalData');
    }
}