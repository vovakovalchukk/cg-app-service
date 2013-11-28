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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service`
--

LOCK TABLES `service` WRITE;
/*!40000 ALTER TABLE `service` DISABLE KEYS */;
INSERT INTO `service` VALUES (1,'test','http://example.com');
/*!40000 ALTER TABLE `service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_event`
--

DROP TABLE IF EXISTS `service_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `instances` int(10) unsigned NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_event_type` (`service_id`,`type`),
  CONSTRAINT `service_event` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_event`
--

LOCK TABLES `service_event` WRITE;
/*!40000 ALTER TABLE `service_event` DISABLE KEYS */;
INSERT INTO `service_event` VALUES (1,1,'valid',1,'http://example.com');
/*!40000 ALTER TABLE `service_event` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-09-17  9:47:54
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
ALTER TABLE `order` ADD CONSTRAINT `order_shippingAddressId` FOREIGN KEY (`shippingAddressId`) REFERENCES `address` (`id`);
ALTER TABLE `order` ADD CONSTRAINT `order_billingAddressId` FOREIGN KEY (`billingAddressId`) REFERENCES `address` (`id`);

INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 1', 'Full Name 1', 'address 1 - 1', 'address 2 - 1', 'address 3 - 1', 'City1', 'County1', 'UK', 'Postcode1', 'emailaddress1@channelgrabber.com', '01942673431', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 1', 'Full Name 1', 'shipping address 1 - 1', 'shipping address 2 - 1', 'shipping address 3 - 1', 'shipping City 1', 'Shipping County 1', 'UK', 'shipPostcode1', 'shippingemail1@channelgrabber.com', '07415878961', 'GB');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`) VALUES ('1411-10', '1411', '10', 'ebay', '1', '21.99', '1', '10.99', 'standard', 'GBP', '0', 'Hello, please leave at the door', '2013-10-10 00:00:00', '2013-10-10 01:00:00', '2013-10-10 10:00:00', '2013-10-10 10:00:10', 1, 2);
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 2', 'Full Name 2', 'address 1 - 2', 'address 2 - 2', 'address 3 - 2', 'City2', 'County2', 'UK', 'Postcode2', 'emailaddress2@channelgrabber.com', '01942673432', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 2', 'Full Name 2', 'shipping address 1 - 2', 'shipping address 2 - 2', 'shipping address 3 - 2', 'shipping City 2', 'Shipping County 2', 'UK', 'shipPostcode2', 'shippingemail2@channelgrabber.com', '07415878962', 'GB');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`) VALUES ('1412-20', '1412', '20', 'ebay2', '2', '22.99', '2', '20.99', 'standard2', 'GBP', '0.02', 'Hello, please leave at the door2', '2013-10-10 00:20:00', '2013-10-10 01:20:00', '2013-10-10 10:20:00', '2013-10-10 10:20:10', 3, 4);
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 3', 'Full Name 3', 'address 1 - 3', 'address 2 - 3', 'address 3 - 3', 'City3', 'County3', 'UK', 'Postcode3', 'emailaddress3@channelgrabber.com', '01942673433', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 3', 'Full Name 3', 'shipping address 1 - 3', 'shipping address 2 - 3', 'shipping address 3 - 3', 'shipping City 3', 'Shipping County 3', 'UK', 'shipPostcode3', 'shippingemail3@channelgrabber.com', '07415878963', 'GB');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`) VALUES ('1413-30', '1413', '30', 'ebay3', '3', '23.99', '3', '30.99', 'standard3', 'GBP', '0.03', 'Hello, please leave at the door3', '2013-10-10 00:30:00', '2013-10-10 01:30:00', '2013-10-10 10:30:00', '2013-10-10 10:30:10', 5, 6);
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 4', 'Full Name 4', 'address 1 - 4', 'address 2 - 4', 'address 3 - 4', 'City4', 'County4', 'UK', 'Postcode4', 'emailaddress4@channelgrabber.com', '01942673434', 'GB');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 4', 'Full Name 4', 'shipping address 1 - 4', 'shipping address 2 - 4', 'shipping address 3 - 4', 'shipping City 4', 'Shipping County 4', 'UK', 'shipPostcode4', 'shippingemail4@channelgrabber.com', '07415878964', 'GB');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`) VALUES ('1414-40', '1414', '40', 'ebay4', '4', '24.99', '4', '40.99', 'standard4', 'GBP', '0.04', 'Hello, please leave at the door4', '2013-10-10 00:40:00', '2013-10-10 01:40:00', '2013-10-10 10:40:00', '2013-10-10 10:40:10', 7, 8);
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Company Name 5', 'Full Name 5', 'address 1 - 5', 'address 2 - 5', 'address 3 - 5', 'City5', 'County5', 'France', 'Postcode5', 'emailaddress5@channelgrabber.com', '01942673435', 'FR');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`, `addressCountryCode`) VALUES ('Shipping Company Name 5', 'Full Name 5', 'shipping address 1 - 5', 'shipping address 2 - 5', 'shipping address 3 - 5', 'shipping City 5', 'Shipping County 5', 'France', 'shipPostcode5', 'shippingemail5@channelgrabber.com', '07415878965', 'FR');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`, `billingAddressId`, `shippingAddressId`) VALUES ('1415-50', '1415', '50', 'ebay5', '5', '25.99', '5', '50.99', 'standard5', 'GBP', '0.05', 'Hello, please leave at the door5', '2013-10-10 00:50:00', '2013-10-10 01:50:00', '2013-10-10 10:50:00', '2013-10-10 10:50:10', 9, 10);

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
INSERT INTO `note` (`id`, `orderId`, `userId`, `note`, `timestamp`) VALUES (5, '1411-10', 5, 'Note 5', '2013-10-10 05:00:00');

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
INSERT INTO `tracking` (`id`, `orderId`, `userId`, `number`, `carrier`, `timestamp`) VALUES (5, '1411-10', 5, '1235', 'carrier 5', '2013-10-10 05:00:00');

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
INSERT INTO `alert` (`id`, `orderId`, `userId`, `alert`, `timestamp`) VALUES (5, '1411-10', 5, 'alert 5', '2013-10-10 05:00:00');

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
INSERT INTO `fee` (`id`, `orderItemId`, `amount`, `name`) VALUES (5, '1411-11', 5.99, 'eBayFee');

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
INSERT INTO `giftWrap` (`id`, `orderItemId`, `giftWrapType`, `giftWrapMessage`, `giftWrapPrice`, `giftWrapTaxPercentage`) VALUES (5, '1411-11', "Standard", 'Wrap Message 5', 5.99, 0.5);
