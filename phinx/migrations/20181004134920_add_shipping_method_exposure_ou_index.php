<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class AddShippingMethodExposureOuIndex extends AbstractOnlineSchemaChange
{
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
