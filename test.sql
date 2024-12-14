-- MySQL dump 10.13  Distrib 8.3.0, for macos14.2 (arm64)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `balances`
--

DROP TABLE IF EXISTS `balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `balances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT 'Описание баланса',
  `paysystem` varchar(255) DEFAULT NULL COMMENT 'Кодовое название баланса',
  `currency` varchar(20) DEFAULT NULL COMMENT 'Тикер курса',
  `status` int NOT NULL DEFAULT '0' COMMENT '1 включена, 0 отключена',
  `type` int NOT NULL DEFAULT '1' COMMENT '1 - фиат, 2 - криптовалюта, 3 - платежная система',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balances`
--

LOCK TABLES `balances` WRITE;
/*!40000 ALTER TABLE `balances` DISABLE KEYS */;
INSERT INTO `balances` VALUES (1,'Main Balance','PaySystem','USD',1,1);
/*!40000 ALTER TABLE `balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatepool`
--

DROP TABLE IF EXISTS `estatepool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estatepool` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date_start` datetime NOT NULL,
  `date_close` datetime NOT NULL,
  `sum` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `sum_goal` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatepool`
--

LOCK TABLES `estatepool` WRITE;
/*!40000 ALTER TABLE `estatepool` DISABLE KEYS */;
INSERT INTO `estatepool` VALUES (1,'2024-12-15 02:38:13','2024-12-15 02:38:13',10.000000,200.000000,0);
/*!40000 ALTER TABLE `estatepool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatepool_gifts`
--

DROP TABLE IF EXISTS `estatepool_gifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estatepool_gifts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_pool` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Имя подарка EN',
  `date_close` varchar(30) NOT NULL COMMENT 'Дата определения победителя',
  `id_winner` int unsigned DEFAULT NULL COMMENT 'ID победителя',
  `id_not_winner` int unsigned DEFAULT NULL COMMENT 'ID точного победителя',
  `priority` int NOT NULL DEFAULT '0' COMMENT 'Нумерация порядка',
  `general` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 простой, 1 основной',
  PRIMARY KEY (`id`),
  KEY `fk_estatepool_gifts_pool` (`id_pool`),
  CONSTRAINT `fk_estatepool_gifts_pool` FOREIGN KEY (`id_pool`) REFERENCES `estatepool` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatepool_gifts`
--

LOCK TABLES `estatepool_gifts` WRITE;
/*!40000 ALTER TABLE `estatepool_gifts` DISABLE KEYS */;
INSERT INTO `estatepool_gifts` VALUES (1,1,'Test','1734215886',NULL,NULL,0,0),(2,1,'Test','2024-12-15 02:48:44',NULL,NULL,0,1);
/*!40000 ALTER TABLE `estatepool_gifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatepool_tickets`
--

DROP TABLE IF EXISTS `estatepool_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estatepool_tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `count` int unsigned NOT NULL DEFAULT '0',
  `sum` decimal(10,6) NOT NULL DEFAULT '0.000000',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatepool_tickets`
--

LOCK TABLES `estatepool_tickets` WRITE;
/*!40000 ALTER TABLE `estatepool_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `estatepool_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatepool_usertickets`
--

DROP TABLE IF EXISTS `estatepool_usertickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estatepool_usertickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket` varchar(9) NOT NULL,
  `id_ticket` int unsigned DEFAULT NULL COMMENT 'ID билета',
  `id_user` int unsigned DEFAULT NULL,
  `id_pool` int unsigned NOT NULL,
  `id_gift` int unsigned NOT NULL COMMENT 'ID подарка',
  `win` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_estatepool_usertickets_pool` (`id_pool`),
  CONSTRAINT `fk_estatepool_usertickets_pool` FOREIGN KEY (`id_pool`) REFERENCES `estatepool` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatepool_usertickets`
--

LOCK TABLES `estatepool_usertickets` WRITE;
/*!40000 ALTER TABLE `estatepool_usertickets` DISABLE KEYS */;
INSERT INTO `estatepool_usertickets` VALUES (1,'4166C3E1',NULL,NULL,1,1,1),(2,'56AFC086',NULL,NULL,1,1,1),(3,'29322F1F',NULL,NULL,1,1,1),(4,'24F29F8E',NULL,NULL,1,1,1);
/*!40000 ALTER TABLE `estatepool_usertickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `id_ref` int DEFAULT '0' COMMENT 'id пригласителя',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'testuser@example.com',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_balances`
--

DROP TABLE IF EXISTS `users_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_balances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL COMMENT 'ID пользователя',
  `id_balance` int unsigned DEFAULT NULL COMMENT 'ID балансов таблица balances',
  `sum` decimal(20,10) NOT NULL DEFAULT '0.0000000000' COMMENT 'Сумма баланса',
  `stat_sum` decimal(20,10) DEFAULT '0.0000000000' COMMENT 'Статистика баланса',
  `status` int NOT NULL DEFAULT '1' COMMENT '1 работает, 0 заблокирован',
  `show_balance` int NOT NULL DEFAULT '1' COMMENT '1 - показан, 0 - спрятан',
  PRIMARY KEY (`id`),
  KEY `fk_users_balances` (`id_balance`),
  CONSTRAINT `fk_users_balances` FOREIGN KEY (`id_balance`) REFERENCES `balances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_balances`
--

LOCK TABLES `users_balances` WRITE;
/*!40000 ALTER TABLE `users_balances` DISABLE KEYS */;
INSERT INTO `users_balances` VALUES (3,1,1,100.0000000000,0.0000000000,1,1);
/*!40000 ALTER TABLE `users_balances` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-15  4:28:11
