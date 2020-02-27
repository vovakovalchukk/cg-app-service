<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddListingExternalIdIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

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
