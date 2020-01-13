<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ProductAndListingNewFields extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    const TABLE_PRODUCT_DETAIL = 'productDetail';
    const TABLE_LISTING = 'listing';

    /** @var string */
    protected $environment;

    public function supportsEnvironment($environment)
    {
        $this->environment = $environment;
        return $environment === 'cg_app' || $environment === 'listings';
    }

    public function up()
    {
        if ($this->environment === 'cg_app') {
            $productDetailAlter = 'ADD COLUMN `description` MEDIUMTEXT AFTER `sku`,'
                . 'ADD COLUMN `ean` VARCHAR(13) AFTER `description`,'
                . 'ADD COLUMN `brand` VARCHAR(100) AFTER `ean`,'
                . 'ADD COLUMN `mpn` VARCHAR(40) AFTER `brand`,'
                . 'ADD COLUMN `asin` VARCHAR(13) AFTER `mpn`,'
                . 'ADD COLUMN `price` DECIMAL(10,4) AFTER `asin`,'
                . 'ADD COLUMN `cost` DECIMAL(10,4) AFTER `price`,'
                . 'ADD COLUMN `condition` VARCHAR(40) AFTER `cost`';

            $this->onlineSchemaChange(static::TABLE_PRODUCT_DETAIL, $productDetailAlter);
        }

        if ($this->environment === 'listings') {
            $listingAlter = 'ADD COLUMN `name` VARCHAR(255) AFTER `accountId`,'
                . 'ADD COLUMN `description` MEDIUMTEXT AFTER `name`,'
                . 'ADD COLUMN `price` DECIMAL(10,4) AFTER `description`,'
                . 'ADD COLUMN `cost` DECIMAL(10,4) AFTER `price`,'
                . 'ADD COLUMN `condition` VARCHAR(40) AFTER `cost`';

            $this->onlineSchemaChange(static::TABLE_LISTING, $listingAlter);
        }
    }

    public function down()
    {
        if ($this->environment === 'cg_app') {
            $productDetailAlter = 'DROP COLUMN `description`,'
                . 'DROP COLUMN `ean`,'
                . 'DROP COLUMN `brand`,'
                . 'DROP COLUMN `mpn`,'
                . 'DROP COLUMN `asin`,'
                . 'DROP COLUMN `price`,'
                . 'DROP COLUMN `cost`,'
                . 'DROP COLUMN `condition`';

            $this->onlineSchemaChange(static::TABLE_PRODUCT_DETAIL, $productDetailAlter);
        }

        if ($this->environment === 'listings') {
            $listingAlter = 'DROP COLUMN `name`,'
                . 'DROP COLUMN `description`,'
                . 'DROP COLUMN `price`,'
                . 'DROP COLUMN `cost`,'
                . 'DROP COLUMN `condition`';

            $this->onlineSchemaChange(static::TABLE_LISTING, $listingAlter);
        }
    }
}
