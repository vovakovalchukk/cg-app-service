<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class AmazonProductDetailFulfilmentLatency extends AbstractOnlineSchemaChange
{
    public function up()
    {
        $this->onlineSchemaChange('productAmazonDetail', 'ADD COLUMN fulfillmentLatency INT');
    }

    public function down()
    {
        $this->onlineSchemaChange('productAmazonDetail', 'DROP COLUMN fulfillmentLatency');
    }
}
