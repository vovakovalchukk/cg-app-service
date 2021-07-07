<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;


class AddShippingAccountIdIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
       $this->onlineSchemaChange('orderLabel', 'ADD INDEX `shippingAccountId_created` (`shippingAccountId` ASC, `created` ASC);');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('orderLabel', 'DROP INDEX `shippingAccountId_created`');
    }
}
