<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ExternalUsernameIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE_ORDER = 'order';
    const TABLE_ORDER_LIVE = 'orderLive';

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "ADD INDEX `ExternalUsername` (`externalUsername`)");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "ADD INDEX `ExternalUsername` (`externalUsername`)");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, "DROP INDEX `ExternalUsername`");
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, "DROP INDEX `ExternalUsername`");
    }
}