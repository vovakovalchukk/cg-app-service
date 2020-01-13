<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonProductDetailFulfilmentLatency extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productAmazonDetail', 'ADD COLUMN fulfillmentLatency INT');
    }

    public function down()
    {
        $this->onlineSchemaChange('productAmazonDetail', 'DROP COLUMN fulfillmentLatency');
    }
}
