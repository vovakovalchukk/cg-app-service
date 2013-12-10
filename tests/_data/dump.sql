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
