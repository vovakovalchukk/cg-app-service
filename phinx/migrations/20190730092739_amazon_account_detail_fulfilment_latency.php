<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AmazonAccountDetailFulfilmentLatency extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productAccountDetail', 'ADD COLUMN externalType VARCHAR(80), ADD COLUMN externalData JSON');
    }

    public function down()
    {
        $this->onlineSchemaChange('productAccountDetail', 'DROP COLUMN externalType, DROP COLUMN externalData');
    }
}