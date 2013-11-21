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
    `addressCountry` varchar(2) NOT NULL,
    `addressPostcode` varchar(20) NOT NULL,
    `emailAddress` varchar(255) NOT NULL,
    `phoneNumber` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
ALTER TABLE `order` ADD CONSTRAINT `order_shippingAddressId` FOREIGN KEY (`shippingAddressId`) REFERENCES `address` (`id`);
ALTER TABLE `order` ADD CONSTRAINT `order_billingAddressId` FOREIGN KEY (`billingAddressId`) REFERENCES `address` (`id`);

INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`) VALUES ('1411-10', '1411', '10', 'ebay', '1', '21.99', '1', '10.99', 'standard', 'GBP', '0', 'Hello, please leave at the door', '2013-10-10 00:00:00', '2013-10-10 01:00:00', '2013-10-10 10:00:00', '2013-10-10 10:00:10');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Company Name 1', 'Full Name 1', 'address 1 - 1', 'address 2 - 1', 'address 3 - 1', 'City1', 'County1', 'Country1', 'Postcode1', 'emailaddress1@channelgrabber.com', '01942673431');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Shipping Company Name 1', 'Full Name 1', 'shipping address 1 - 1', 'shipping address 2 - 1', 'shipping address 3 - 1', 'shipping City 1', 'Shipping County 1', 'United Kingdom', 'shipPostcode1', 'shippingemail1@channelgrabber.com', '07415878961');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`) VALUES ('1411-20', '1412', '20', 'ebay2', '2', '22.99', '2', '20.99', 'standard2', 'GBP2', '0.02', 'Hello, please leave at the door2', '2013-10-10 00:20:00', '2013-10-10 01:20:00', '2013-10-10 10:20:00', '2013-10-10 10:20:10');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Company Name 2', 'Full Name 2', 'address 1 - 2', 'address 2 - 2', 'address 3 - 2', 'City2', 'County2', 'Country2', 'Postcode2', 'emailaddress2@channelgrabber.com', '01942673432');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Shipping Company Name 2', 'Full Name 2', 'shipping address 1 - 2', 'shipping address 2 - 2', 'shipping address 3 - 2', 'shipping City 2', 'Shipping County 2', 'United Kingdom', 'shipPostcode2', 'shippingemail2@channelgrabber.com', '07415878962');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`) VALUES ('1411-30', '1411', '30', 'ebay', '1', '23.99', '1', '30.99', 'standard', 'GBP3', '0.03', 'Hello, please leave at the door3', '2013-10-10 00:30:00', '2013-10-10 01:30:00', '2013-10-10 10:30:00', '2013-10-10 10:30:10');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Company Name 3', 'Full Name 3', 'address 1 - 3', 'address 2 - 3', 'address 3 - 3', 'City3', 'County3', 'Country3', 'Postcode3', 'emailaddress3@channelgrabber.com', '01942673433');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Shipping Company Name 3', 'Full Name 3', 'shipping address 1 - 3', 'shipping address 2 - 3', 'shipping address 3 - 3', 'shipping City 3', 'Shipping County 3', 'United Kingdom', 'shipPostcode3', 'shippingemail3@channelgrabber.com', '07415878963');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`) VALUES ('1414-40', '1414', '40', 'ebay4', '4', '24.99', '4', '40.99', 'standard4', 'GBP4', '0.04', 'Hello, please leave at the door4', '2013-10-10 00:40:00', '2013-10-10 01:40:00', '2013-10-10 10:40:00', '2013-10-10 10:40:10');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Company Name 4', 'Full Name 4', 'address 1 - 4', 'address 2 - 4', 'address 3 - 4', 'City4', 'County4', 'Country4', 'Postcode4', 'emailaddress4@channelgrabber.com', '01942673434');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Shipping Company Name 4', 'Full Name 4', 'shipping address 1 - 4', 'shipping address 2 - 4', 'shipping address 3 - 4', 'shipping City 4', 'Shipping County 4', 'France', 'shipPostcode4', 'shippingemail4@channelgrabber.com', '07415878964');
INSERT INTO `order` (`id`, `accountId`, `externalId`, `channel`, `organisationUnitId`, `total`, `status`, `shippingPrice`, `shippingMethod`, `currencyCode`, `totalDiscount`, `buyerMessage`, `purchaseDate`, `paymentDate`, `printedDate`, `dispatchDate`) VALUES ('1415-50', '1415', '50', 'ebay5', '5', '25.99', '5', '50.99', 'standard5', 'GBP5', '0.05', 'Hello, please leave at the door5', '2013-10-10 00:50:00', '2013-10-10 01:50:00', '2013-10-10 10:50:00', '2013-10-10 10:50:10');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Company Name 5', 'Full Name 5', 'address 1 - 5', 'address 2 - 5', 'address 3 - 5', 'City5', 'County5', 'Country5', 'Postcode5', 'emailaddress5@channelgrabber.com', '01942673435');
INSERT INTO `address` (`addressCompanyName`, `addressFullName`, `address1`, `address2`, `address3`, `addressCity`, `addressCounty`, `addressCountry`, `addressPostcode`, `emailAddress`, `phoneNumber`) VALUES ('Shipping Company Name 5', 'Full Name 5', 'shipping address 1 - 5', 'shipping address 2 - 5', 'shipping address 3 - 5', 'shipping City 5', 'Shipping County 5', 'France', 'shipPostcode5', 'shippingemail5@channelgrabber.com', '07415878965');