<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingModifiedTime extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE = 'listing';

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }

    public function up()
    {
        $this->onlineSchemaChange(static::TABLE, 'ADD COLUMN lastModified DATETIME');
    }

    public function down()
    {
        $this->onlineSchemaChange(static::TABLE, 'DROP COLUMN lastModified');
    }
}