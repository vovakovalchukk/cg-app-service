<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\EnvironmentAwareInterface;

class OrderApiMigration extends AbstractMigration implements EnvironmentAwareInterface
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
        $sql = "CREATE TABLE IF NOT EXISTS `order` (
                      `id` varchar(120) NOT NULL,
                      `accountId` int(10) unsigned NOT NULL,
                      `externalId` varchar(120) NOT NULL,
                      `organisationUnitId` int(10) unsigned NOT NULL,
                      `channel` varchar(40) NOT NULL,
                      `total` decimal(12,4) NOT NULL,
                      `status` varchar(20) NOT NULL,
                      `shippingPrice` decimal(12,4) NOT NULL,
                      `shippingMethod` varchar(80) NOT NULL,
                      `currencyCode` varchar(3) NOT NULL,
                      `totalDiscount` decimal(12,4) NOT NULL,
                      `buyerMessage` varchar(255) NOT NULL,
                      `purchaseDate` datetime NOT NULL,
                      `paymentDate` datetime NOT NULL,
                      `printedDate` datetime NOT NULL,
                      `dispatchDate` datetime NOT NULL,
                      `billingAddressId` int(10) unsigned DEFAULT NULL,
                      `shippingAddressId` int(10) unsigned DEFAULT NULL,
                      `batch` int(10) unsigned DEFAULT NULL,
                      `paymentMethod` varchar(120) DEFAULT NULL,
                      `paymentReference` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `billingAddressId` (`billingAddressId`),
                      KEY `shippingAddressId` (`shippingAddressId`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `address` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `addressCompanyName` varchar(255) NOT NULL,
                  `addressFullName` varchar(255) NOT NULL,
                  `address1` varchar(255) NOT NULL,
                  `address2` varchar(255) NOT NULL,
                  `address3` varchar(255) NOT NULL,
                  `addressCity` varchar(255) NOT NULL,
                  `addressCounty` varchar(255) NOT NULL,
                  `addressCountry` varchar(255) NOT NULL,
                  `addressPostcode` varchar(20) NOT NULL,
                  `emailAddress` varchar(255) NOT NULL,
                  `phoneNumber` varchar(20) NOT NULL,
                  `addressCountryCode` varchar(2) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);

        $sql = "ALTER TABLE `order` ADD CONSTRAINT `order_shippingAddressId` FOREIGN KEY (`shippingAddressId`) REFERENCES `address` (`id`)";
        $this->execute($sql);
        $sql = "ALTER TABLE `order` ADD CONSTRAINT `order_billingAddressId` FOREIGN KEY (`billingAddressId`) REFERENCES `address` (`id`)";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `note` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `note` text NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `tracking` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `number` varchar(120) NOT NULL,
                  `carrier` varchar(120) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `alert` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `alert` text NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `fee` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderItemId` varchar(120) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `amount` decimal(12,4) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderItemId` (`orderItemId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `giftWrap` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderItemId` varchar(120) NOT NULL,
                  `giftWrapType` varchar(120) NOT NULL,
                  `giftWrapMessage` varchar(120) NOT NULL,
                  `giftWrapPrice` decimal(12, 4) NOT NULL,
                  `giftWrapTaxPercentage` decimal(6, 5) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderItemId` (`orderItemId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $sql = 'DROP TABLE IF EXISTS `order`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `address`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `note`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `tracking`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `alert`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `fee`';
        $this->execute($sql);
        $sql = 'DROP TABLE IF EXISTS `giftWrap`';
        $this->execute($sql);
    }
}