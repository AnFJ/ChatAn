-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: localhost    Database: chatan
-- ------------------------------------------------------
-- Server version	5.7.17-log

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
-- Table structure for table `friend_request`
--

DROP TABLE IF EXISTS `friend_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friend_request` (
  `source_account` varchar(100) NOT NULL,
  `target_account` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friend_request`
--

LOCK TABLES `friend_request` WRITE;
/*!40000 ALTER TABLE `friend_request` DISABLE KEYS */;
INSERT INTO `friend_request` VALUES ('2','8'),('8','2');
/*!40000 ALTER TABLE `friend_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_info`
--

DROP TABLE IF EXISTS `group_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_info` (
  `group_id` int(10) NOT NULL AUTO_INCREMENT,
  `group_account` varchar(100) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_lord` varchar(100) DEFAULT NULL,
  `group_info` varchar(200) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`group_account`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_info`
--

LOCK TABLES `group_info` WRITE;
/*!40000 ALTER TABLE `group_info` DISABLE KEYS */;
INSERT INTO `group_info` VALUES (19,'1','一群','8',NULL,'2017-04-13 17:12:40'),(22,'2','二群','1',NULL,'2017-04-29 17:37:59'),(23,'3','三群','1',NULL,'2017-05-01 13:37:46'),(20,'4','四群','2',NULL,'2017-04-29 15:40:31');
/*!40000 ALTER TABLE `group_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_members` (
  `group_account` varchar(100) NOT NULL,
  `member_account` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES ('1','8'),('1','1'),('4','2'),('2','1'),('3','1'),('4','1'),('3','8');
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_request`
--

DROP TABLE IF EXISTS `group_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_request` (
  `request_account` varchar(100) DEFAULT NULL,
  `group_account` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_request`
--

LOCK TABLES `group_request` WRITE;
/*!40000 ALTER TABLE `group_request` DISABLE KEYS */;
INSERT INTO `group_request` VALUES ('','2');
/*!40000 ALTER TABLE `group_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `online_user`
--

DROP TABLE IF EXISTS `online_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_user` (
  `user_account` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `online_user`
--

LOCK TABLES `online_user` WRITE;
/*!40000 ALTER TABLE `online_user` DISABLE KEYS */;
INSERT INTO `online_user` VALUES ('15736641436'),('2');
/*!40000 ALTER TABLE `online_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_friends`
--

DROP TABLE IF EXISTS `user_friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_friends` (
  `user_account` varchar(100) NOT NULL,
  `friend_account` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_friends`
--

LOCK TABLES `user_friends` WRITE;
/*!40000 ALTER TABLE `user_friends` DISABLE KEYS */;
INSERT INTO `user_friends` VALUES ('1','3'),('3','1'),('1','4'),('4','1'),('1','5'),('5','1'),('1','6'),('6','1'),('2','3'),('3','2'),('2','4'),('4','2'),('2','5'),('5','2'),('2','6'),('6','2'),('3','4'),('4','3'),('3','5'),('5','3'),('3','6'),('6','3'),('1','7'),('7','1'),('1','1'),('1','1'),('2','1'),('1','2'),('8','1'),('1','8');
/*!40000 ALTER TABLE `user_friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_info` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_account` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_password` varchar(40) NOT NULL,
  `login_date` datetime DEFAULT NULL,
  `user_info` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`user_account`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES (19,'1','小一','1','2017-05-28 12:31:35',NULL),(20,'2','小二','2','2017-05-26 15:04:44',NULL),(21,'3','小三','3','2017-05-25 13:11:59',NULL),(22,'4','小四','4','2017-04-30 17:53:44',NULL),(23,'5','小五','5','2017-04-07 14:45:34',NULL),(24,'6','小六','6','2017-04-07 00:00:00',NULL),(25,'7','小七','7','2017-04-07 14:42:38',NULL),(26,'8','小八','8','2017-05-26 10:54:14',NULL);
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-20 23:25:18
