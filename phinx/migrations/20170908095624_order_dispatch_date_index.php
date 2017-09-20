<?php
use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderDispatchDateIndex extends AbstractOnlineSchemaChange
{
    const TABLE_ORDER = 'order';
    const TABLE_ORDER_LIVE = 'orderLive';

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, 'ADD INDEX `DispatchDateOrganisationUnitIdAccountId` (`dispatchDate`, `organisationUnitId`, `accountId`)');
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, 'ADD INDEX `DispatchDateOrganisationUnitIdAccountId` (`dispatchDate`, `organisationUnitId`, `accountId`)');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange(static::TABLE_ORDER, 'DROP INDEX `DispatchDateOrganisationUnitIdAccountId`');
        $this->onlineSchemaChange(static::TABLE_ORDER_LIVE, 'DROP INDEX `DispatchDateOrganisationUnitIdAccountId`');
    }
}