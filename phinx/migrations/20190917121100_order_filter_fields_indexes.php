<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderFilterFieldsIndexes extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
        $indexes = [
            'ADD INDEX `OuPurchaseDate` (`organisationUnitId`, `purchaseDate`)',
            'ADD INDEX `OuPaymentDate` (`organisationUnitId`, `paymentDate`)',
            'ADD INDEX `OuDispatchDate` (`organisationUnitId`, `dispatchDate`)',
            'ADD INDEX `OuStatus` (`organisationUnitId`, `status`)',
            'ADD INDEX `OuCurrencyCode` (`organisationUnitId`, `currencyCode`)',
            'ADD INDEX `OuTotal` (`organisationUnitId`, `total`)',
            'ADD INDEX `OuChannel` (`organisationUnitId`, `channel`)',
            'ADD INDEX `OuWeight` (`organisationUnitId`, `weight`)',
            'ADD INDEX `OuAccountId` (`organisationUnitId`, `accountId`)',
            'ADD INDEX `OuShippingMethod` (`organisationUnitId`, `shippingMethod`(200))',
            'ADD INDEX `OuFulfilmentChannel` (`organisationUnitId`, `fulfilmentChannel`)',
            'ADD INDEX `OuMarketplace` (`organisationUnitId`, `marketplace`)',
        ];

        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, implode(', ', $indexes));
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $indexes = [
            'DROP INDEX `OuPurchaseDate`',
            'DROP INDEX `OuPaymentDate`',
            'DROP INDEX `OuDispatchDate`',
            'DROP INDEX `OuStatus`',
            'DROP INDEX `OuCurrencyCode`',
            'DROP INDEX `OuTotal`',
            'DROP INDEX `OuChannel`',
            'DROP INDEX `OuWeight`',
            'DROP INDEX `OuAccountId`',
            'DROP INDEX `OuShippingMethod`',
            'DROP INDEX `OuFulfilmentChannel`',
            'DROP INDEX `OuMarketplace`',
        ];

        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange($table, implode(', ', $indexes));
        }
    }
}