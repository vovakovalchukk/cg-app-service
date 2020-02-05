<?php
use Phinx\Migration\EnvironmentAwareInterface;
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderMarketplace extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('order', 'ADD COLUMN `marketplace` varchar(255) NULL');
        $this->onlineSchemaChange('orderLive', 'ADD COLUMN `marketplace` varchar(255) NULL');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('order', 'DROP COLUMN `marketplace`');
        $this->onlineSchemaChange('orderLive', 'DROP COLUMN `marketplace`');
    }
}
