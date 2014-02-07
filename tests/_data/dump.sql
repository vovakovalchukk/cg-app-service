-- MySQL dump 10.13  Distrib 5.5.33, for Linux (x86_64)
--
-- Host: localhost    Database: cg_app
-- ------------------------------------------------------
-- Server version	5.5.33-31.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `version` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
INSERT INTO `migration` VALUES ('20130917084909'),('20130917085544');
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `service` VALUES (1,'Type1','endpoint1');
INSERT INTO `service` VALUES (2,'Type2','endpoint2');
INSERT INTO `service` VALUES (3,'Type3','endpoint3');
INSERT INTO `service` VALUES (4,'Type4','endpoint4');
INSERT INTO `service` VALUES (5,'Type5','endpoint5');

DROP TABLE IF EXISTS `serviceEvent`;
CREATE TABLE `serviceEvent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serviceId` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `instances` int(10) unsigned NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `serviceEvent` FOREIGN KEY (`serviceId`) REFERENCES `service` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `serviceEvent` VALUES (1,1,'type1',1,'http://example1.com');
INSERT INTO `serviceEvent` VALUES (2,1,'type2',2,'http://example2.com');
INSERT INTO `serviceEvent` VALUES (3,1,'type3',3,'http://example3.com');
INSERT INTO `serviceEvent` VALUES (4,1,'type4',4,'http://example4.com');
INSERT INTO `serviceEvent` VALUES (5,1,'type5',5,'http://example5.com');
INSERT INTO `serviceEvent` VALUES (6,2,'type6',6,'http://example6.com');

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
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
    `batch` int (10) unsigned DEFAULT NULL,
    `paymentMethod` varchar(120) DEFAULT NULL,
    `paymentReference` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `billingAddressId` (`billingAddressId`),
    KEY `shippingAddressId` (`shippingAddressId`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `address`;
CREATE TABLE IF NOT EXISTS `address` (
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
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
DROP TABLE IF EXISTS `orderTag`;
CREATE TABLE IF NOT EXISTS `orderTag` (
    `id` varchar(120) NOT NULL,
    `orderId` varchar(120) NOT NULL,
    `orderTag` varchar(120) NOT NULL,
    `organisationUnitId` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `order` ADD CONSTRAINT `order_shippingAddressId` FOREIGN KEY (`shippingAddressId`) REFERENCES `address` (`id`);
ALTER TABLE `order` ADD CONSTRAINT `order_billingAddressId` FOREIGN KEY (`billingAddressId`) REFERENCES `address` (`id`);

INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 1', 'Full Name 1', 'address 1 - 1', 'address 2 - 1', 'address 3 - 1', 'City1', 'County1', 'UK', 'Postcode1', 'emailaddress1@channelgrabber.com', '01942673431', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 1', 'Full Name 1', 'shipping address 1 - 1', 'shipping address 2 - 1', 'shipping address 3 - 1', 'shipping City 1', 'Shipping County 1', 'UK', 'shipPostcode1', 'shippingemail1@channelgrabber.com', '07415878961', 'GB');
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1411-10-tag1','1411-10', 'tag1', 1);
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1411-10-tag2','1411-10', 'tag2', 1);
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1411-10-tag5','1411-10', 'tag5', 1);
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`, `batch`, `paymentMethod`, `paymentReference`) VALUES ('1411-10', '1411', '10', 'ebay', '1', '21.99', '1', '10.99', 'standard', 'GBP', '0', 'Hello, please leave at the door', '2013-10-10 00:00:00', '2013-10-10 01:00:00', '2013-10-10 10:00:00', '2013-10-10 10:00:10', 1, 2, 1, 'paymentMethod1', 'paymentReference1');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 2', 'Full Name 2', 'address 1 - 2', 'address 2 - 2', 'address 3 - 2', 'City2', 'County2', 'UK', 'Postcode2', 'emailaddress2@channelgrabber.com', '01942673432', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 2', 'Full Name 2', 'shipping address 1 - 2', 'shipping address 2 - 2', 'shipping address 3 - 2', 'shipping City 2', 'Shipping County 2', 'UK', 'shipPostcode2', 'shippingemail2@channelgrabber.com', '07415878962', 'GB');
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1412-20-tag2','1412-20', 'tag2', 2);
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1412-20-tag3','1412-20', 'tag3', 2);
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`, `batch`, `paymentMethod`, `paymentReference`) VALUES ('1412-20', '1412', '20', 'ebay2', '2', '22.99', '2', '20.99', 'standard2', 'GBP', '0.02', 'Hello, please leave at the door2', '2013-10-10 00:20:00', '2013-10-10 01:20:00', '2013-10-10 10:20:00', '2013-10-10 10:20:10', 3, 4, 1, 'paymentMethod2', 'paymentReference2');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 3', 'Full Name 3', 'address 1 - 3', 'address 2 - 3', 'address 3 - 3', 'City3', 'County3', 'UK', 'Postcode3', 'emailaddress3@channelgrabber.com', '01942673433', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 3', 'Full Name 3', 'shipping address 1 - 3', 'shipping address 2 - 3', 'shipping address 3 - 3', 'shipping City 3', 'Shipping County 3', 'UK', 'shipPostcode3', 'shippingemail3@channelgrabber.com', '07415878963', 'GB');
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1413-30-tag3','1413-30', 'tag3', 3);
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1413-30-tag4','1413-30', 'tag4', 3);
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`, `batch`, `paymentMethod`, `paymentReference`) VALUES ('1413-30', '1413', '30', 'ebay3', '3', '23.99', '3', '30.99', 'standard3', 'GBP', '0.03', 'Hello, please leave at the door3', '2013-10-10 00:30:00', '2013-10-10 01:30:00', '2013-10-10 10:30:00', '2013-10-10 10:30:10', 5, 6, 1, 'paymentMethod3', 'paymentReference3');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 4', 'Full Name 4', 'address 1 - 4', 'address 2 - 4', 'address 3 - 4', 'City4', 'County4', 'UK', 'Postcode4', 'emailaddress4@channelgrabber.com', '01942673434', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 4', 'Full Name 4', 'shipping address 1 - 4', 'shipping address 2 - 4', 'shipping address 3 - 4', 'shipping City 4', 'Shipping County 4', 'UK', 'shipPostcode4', 'shippingemail4@channelgrabber.com', '07415878964', 'GB');
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1414-40-tag4','1414-40', 'tag4', 4);
INSERT INTO `orderTag` (`id`, `orderId`, `orderTag`, `organisationUnitId`) VALUES ('1414-40-tag5','1414-40', 'tag5', 4);
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`, `batch`, `paymentMethod`, `paymentReference`) VALUES ('1414-40', '1414', '40', 'ebay4', '4', '24.99', '4', '40.99', 'standard4', 'GBP', '0.04', 'Hello, please leave at the door4', '2013-10-10 00:40:00', '2013-10-10 01:40:00', '2013-10-10 10:40:00', '2013-10-10 10:40:10', 7, 8, 1, 'paymentMethod4', 'paymentReference4');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 5', 'Full Name 5', 'address 1 - 5', 'address 2 - 5', 'address 3 - 5', 'City5', 'County5', 'France', 'Postcode5', 'emailaddress5@channelgrabber.com', '01942673435', 'FR');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 5', 'Full Name 5', 'shipping address 1 - 5', 'shipping address 2 - 5', 'shipping address 3 - 5', 'shipping City 5', 'Shipping County 5', 'France', 'shipPostcode5', 'shippingemail5@channelgrabber.com', '07415878965', 'FR');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`, `batch`, `paymentMethod`, `paymentReference`) VALUES ('1415-50', '1415', '50', 'ebay5', '5', '25.99', '5', '50.99', 'standard5', 'GBP', '0.05', 'Hello, please leave at the door5', '2013-10-10 00:50:00', '2013-10-10 01:50:00', '2013-10-10 10:50:00', '2013-10-10 10:50:10', 9, 10, 2, 'paymentMethod5', 'paymentReference5');

CREATE TABLE IF NOT EXISTS `note` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `note` text NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (1, '1411-10', 1, 'Note 1', '2013-10-10 01:00:00');
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (2, '1411-10', 2, 'Note 2', '2013-10-10 02:00:00');
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (3, '1411-10', 3, 'Note 3', '2013-10-10 03:00:00');
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (4, '1411-10', 4, 'Note 4', '2013-10-10 04:00:00');
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (5, '1412-20', 5, 'Note 5', '2013-10-10 05:00:00');
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (6, '1411-10', 6, 'Note 6', '2013-10-10 06:00:00');


CREATE TABLE IF NOT EXISTS `tracking` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `number` varchar(120) NOT NULL,
                  `carrier` varchar(120) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (1, '1411-10', 1, '1231', 'carrier 1', '2013-10-10 01:00:00');
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (2, '1411-10', 2, '1232', 'carrier 2', '2013-10-10 02:00:00');
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (3, '1411-10', 3, '1233', 'carrier 3', '2013-10-10 03:00:00');
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (4, '1411-10', 4, '1234', 'carrier 4', '2013-10-10 04:00:00');
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (5, '1412-20', 5, '1235', 'carrier 5', '2013-10-10 05:00:00');
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (6, '1411-10', 6, '1236', 'carrier 6', '2013-10-10 06:00:00');


CREATE TABLE IF NOT EXISTS `alert` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderId` varchar(120) NOT NULL,
                  `userId` int(10) unsigned NOT NULL,
                  `timestamp` datetime NOT NULL,
                  `alert` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderId` (`orderId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (1, '1411-10', 1, 'alert 1', '2013-10-10 01:00:00');
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (2, '1411-10', 2, 'alert 2', '2013-10-10 02:00:00');
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (3, '1411-10', 3, 'alert 3', '2013-10-10 03:00:00');
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (4, '1411-10', 4, 'alert 4', '2013-10-10 04:00:00');
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (5, '1412-20', 5, 'alert 5', '2013-10-10 05:00:00');
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (6, '1411-10', 6, 'alert 6', '2013-10-10 06:00:00');

CREATE TABLE IF NOT EXISTS `fee` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderItemId` varchar(120) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `amount` decimal(12,4) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderItemId` (`orderItemId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (1, '1411-11', 1.99, 'eBayFee');
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (2, '1411-11', 2.99, 'eBayFee');
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (3, '1411-11', 3.99, 'eBayFee');
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (4, '1411-11', 4.99, 'eBayFee');
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (5, '1411-12', 5.99, 'eBayFee');
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (6, '1411-11', 6.99, 'eBayFee');

CREATE TABLE IF NOT EXISTS `giftWrap` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `orderItemId` varchar(120) NOT NULL,
                  `giftWrapType` varchar(120) NOT NULL,
                  `giftWrapMessage` varchar(120) NOT NULL,
                  `giftWrapPrice` decimal(12, 4) NOT NULL,
                  `giftWrapTaxPercentage` decimal(12, 4) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `orderItemId` (`orderItemId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (1, '1411-11', "Standard", 'Wrap Message 1', 1.99, 0.1);
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (2, '1411-11', "Standard", 'Wrap Message 2', 2.99, 0.2);
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (3, '1411-11', "Standard", 'Wrap Message 3', 3.99, 0.3);
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (4, '1411-11', "Standard", 'Wrap Message 4', 4.99, 0.4);
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (5, '1411-12', "Standard", 'Wrap Message 5', 5.99, 0.5);
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (6, '1411-11', "Standard", 'Wrap Message 6', 6.99, 0.6);

CREATE TABLE IF NOT EXISTS `batch` (
                      `id` varchar(30) NOT NULL,
                      `name` int(10) unsigned NOT NULL,
                      `organisationUnitId` int(10) unsigned NOT NULL,
                      `active` tinyint(1),
                      PRIMARY KEY (`id`),
                      KEY `organisationUnitId-name` (`organisationUnitId`, `name`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (1, 1, "1-1", true);
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (2, 1, "1-2", true);
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (3, 1, "1-3", true);
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (4, 1, "1-4", true);
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (5, 1, "1-5", false);
INSERT INTO `batch` (`name`, `organisationUnitId`, `id`, `active`) VALUES (1, 2, "2-1", true);

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `id` varchar(120) NOT NULL,
  `orderId` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-11', '1411-10');
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-12', '1411-10');
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-13', '1411-10');
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-44', '1411-10');
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-45', '1412-20');
INSERT INTO `item` (`id`, `orderId`) VALUES ('1411-46', '1411-10');