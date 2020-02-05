<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderFulfilmentAddressIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLES = ['order', 'orderLive'];

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'ADD INDEX `fulfilmentAddressId` (`fulfilmentAddressId`)');
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, 'DROP INDEX `fulfilmentAddressId`');
        }
    }
}