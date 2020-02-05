<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class GiftMessageRedaction extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
        $this->onlineSchemaChange('giftWrap', 'ADD COLUMN `giftWrapRedacted` BOOLEAN NOT NULL DEFAULT FALSE AFTER `orderItemId`');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('giftWrap', 'DROP COLUMN `giftWrapRedacted`');
    }
}