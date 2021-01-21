<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class TrackingShippingService extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('tracking', 'ADD COLUMN `shippingService` VARCHAR(120) DEFAULT NULL');
    }

    public function down()
    {
        $this->onlineSchemaChange('tracking', 'DROP COLUMN `shippingService`');
    }
}
