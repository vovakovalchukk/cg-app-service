<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddShippingMethodExposureOuIndex extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange(
            'shippingMethodExposure',
            'ADD INDEX organisationUnitId (organisationUnitId)'
        );
    }

    public function down()
    {
        $this->onlineSchemaChange(
            'shippingMethodExposure',
            'DROP INDEX `organisationUnitId`'
        );
    }
}
