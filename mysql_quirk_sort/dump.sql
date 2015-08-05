-- MySQL dump 10.13  Distrib 5.6.19, for Linux (x86_64)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	5.5.41-MariaDB

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
-- Table structure for table `test_sort_table`
--

DROP TABLE IF EXISTS `test_sort_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_sort_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `content` text COMMENT 'content',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'status (0: hide; 1: public)',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'sort_larger_is_higer',
  PRIMARY KEY (`id`),
  KEY `Index_sort` (`status`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='test_sort_table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_sort_table`
--

LOCK TABLES `test_sort_table` WRITE;
/*!40000 ALTER TABLE `test_sort_table` DISABLE KEYS */;
INSERT INTO `test_sort_table` VALUES (1,'',1,1),(2,'',1,22),(3,'',0,0),(4,'',1,0),(5,'',1,0),(6,'',1,0),(7,'',1,0),(8,'',1,0),(9,'',1,0),(10,'',1,0),(11,'',1,0),(12,'',1,0),(13,'',1,0),(14,'',1,0),(15,'',1,0),(16,'',1,0),(17,'',1,0),(18,'',1,0),(19,'',1,0),(20,'',1,0),(21,'',1,0),(22,'',1,0),(23,'',1,0),(24,'',1,0),(25,'',1,0),(26,'',1,0),(27,'',1,0),(28,'',1,0),(29,'',1,0),(30,'',1,0),(31,'',1,0),(32,'',1,0),(33,'',1,0),(34,'',1,0),(35,'',1,0),(36,'',1,0),(37,'',1,0),(38,'',1,0),(39,'',1,0),(40,'',1,0),(41,'',1,0),(42,'',1,0);
/*!40000 ALTER TABLE `test_sort_table` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-08-05 12:53:59
