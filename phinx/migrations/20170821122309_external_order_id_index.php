<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ExternalOrderIdIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE_ORDER = 'order';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "ADD INDEX `externalId` (externalId), ADD INDEX `channel` (channel, externalId)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "DROP INDEX `externalId`, DROP INDEX `channel`");
    }
}
