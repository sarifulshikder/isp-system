-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: isp_db
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  `username` varchar(60) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,NULL,'login','Admin login successful',NULL,'2026-05-20 16:07:50',1,'admin','103.112.150.52'),(2,NULL,'login','Admin login successful',NULL,'2026-05-20 16:08:01',1,'admin','103.112.150.52'),(3,NULL,'login','Admin login successful',NULL,'2026-05-20 16:09:35',1,'admin','103.112.150.52'),(4,NULL,'login','Admin login successful',NULL,'2026-05-20 16:12:07',1,'admin','103.112.150.52'),(5,NULL,'login','Admin login successful',NULL,'2026-05-20 16:12:38',1,'admin','103.112.150.52'),(6,NULL,'login','Admin login successful',NULL,'2026-05-20 21:35:40',1,'admin','103.112.150.150'),(7,NULL,'login','Admin login successful',NULL,'2026-05-20 22:36:24',1,'admin','103.112.150.52'),(8,NULL,'login','Admin login successful',NULL,'2026-05-20 22:36:39',1,'admin','103.112.150.52'),(9,NULL,'login','Admin login successful',NULL,'2026-05-21 00:23:37',1,'admin','103.112.150.52'),(10,NULL,'login','Admin login successful',NULL,'2026-05-21 01:05:48',1,'admin','103.112.150.52'),(11,NULL,'login','Admin login successful',NULL,'2026-05-21 08:02:31',1,'admin','103.112.150.52'),(12,NULL,'login','Admin login successful',NULL,'2026-05-21 09:03:13',1,'admin','103.112.150.150'),(13,NULL,'login','Admin login successful',NULL,'2026-05-21 09:13:42',1,'admin','103.112.150.150'),(14,NULL,'login','Admin login successful',NULL,'2026-05-21 09:22:59',1,'admin','103.112.150.150'),(15,NULL,'login','Admin login successful',NULL,'2026-05-21 09:42:50',1,'admin','103.112.150.150'),(16,NULL,'login','Admin login successful',NULL,'2026-05-21 10:43:57',1,'admin','103.112.150.150'),(17,NULL,'login','Admin login successful',NULL,'2026-05-21 11:48:50',1,'admin','103.112.150.150'),(18,NULL,'login','Admin login successful',NULL,'2026-05-21 11:49:32',1,'admin','103.112.150.150'),(19,NULL,'login','Admin login successful',NULL,'2026-05-21 13:23:21',1,'admin','103.112.150.52'),(20,NULL,'login','Admin login successful',NULL,'2026-05-21 14:36:58',1,'admin','103.112.150.52'),(21,NULL,'login','Admin login successful',NULL,'2026-05-21 15:04:34',1,'admin','103.112.150.52'),(22,NULL,'login','Admin login successful',NULL,'2026-05-21 15:06:08',1,'admin','103.112.150.52'),(23,NULL,'login','Admin login successful',NULL,'2026-05-21 18:02:23',1,'admin','103.112.150.150'),(24,NULL,'login','Admin login successful',NULL,'2026-05-21 21:38:57',1,'admin','103.112.150.52'),(25,NULL,'login','Admin login successful',NULL,'2026-05-21 22:40:37',1,'admin','103.112.150.52'),(26,NULL,'login','Admin login successful',NULL,'2026-05-21 23:27:50',1,'admin','103.112.150.52'),(27,NULL,'login','Admin login successful',NULL,'2026-05-22 00:29:42',1,'admin','103.112.150.52');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','manager','support') DEFAULT 'support',
  `branch_id` int unsigned DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$nXhwK/tsXmgDHrCcZZQBLOdSn0YZU7FnPmRKhDSa.X0iGvSAmUeu.','superadmin',1,NULL,NULL,'active','2026-05-20 15:52:29');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_invoice_log`
--

DROP TABLE IF EXISTS `auto_invoice_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_invoice_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_invoice_log`
--

LOCK TABLES `auto_invoice_log` WRITE;
/*!40000 ALTER TABLE `auto_invoice_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_invoice_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_invoices`
--

DROP TABLE IF EXISTS `billing_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(30) NOT NULL,
  `customer_id` int unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `billing_invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_invoices`
--

LOCK TABLES `billing_invoices` WRITE;
/*!40000 ALTER TABLE `billing_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Main Branch','Dhaka, Bangladesh','01700000000','active','2026-05-20 15:52:29');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_subscriptions`
--

DROP TABLE IF EXISTS `customer_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_subscriptions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `plan_id` int unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','cancelled','expired') DEFAULT 'active',
  `auto_renew` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `customer_subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_subscriptions`
--

LOCK TABLES `customer_subscriptions` WRITE;
/*!40000 ALTER TABLE `customer_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `plan_id` int unsigned DEFAULT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `expiry` date NOT NULL,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active',
  `onu_serial` varchar(60) DEFAULT NULL,
  `vlan` smallint unsigned DEFAULT NULL,
  `olt` varchar(60) DEFAULT NULL,
  `olt_port` tinyint unsigned DEFAULT NULL,
  `master_box` varchar(60) DEFAULT NULL,
  `db_box` varchar(60) DEFAULT NULL,
  `db_port` tinyint unsigned DEFAULT NULL,
  `wifi_ssid` varchar(60) DEFAULT NULL,
  `wifi_password` varchar(60) DEFAULT NULL,
  `wallet_balance` decimal(10,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `plan_id` (`plan_id`),
  KEY `branch_id` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry` (`expiry`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'joshim001','$2y$10$1sFqgeAIB.q1Rb6yUV5sauG1mjXtA.roeND39Hpacy4r2zXMO.8Mm','Joshim Mridha','01318474150','joshim@email.com','Gucchogram, Mathavanga',22.76200900,89.56794100,1,NULL,'2026-06-20','active','',511,'0',1,'','',0,'joshim001','5acb9174df',0.00,'2026-05-21 01:26:19');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_usage`
--

DROP TABLE IF EXISTS `data_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_usage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned NOT NULL,
  `username` varchar(64) DEFAULT NULL,
  `upload` bigint unsigned DEFAULT '0',
  `download` bigint unsigned DEFAULT '0',
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `used_quota` bigint unsigned GENERATED ALWAYS AS ((`upload` + `download`)) STORED,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  CONSTRAINT `data_usage_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_usage`
--

LOCK TABLES `data_usage` WRITE;
/*!40000 ALTER TABLE `data_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diary_comments`
--

DROP TABLE IF EXISTS `diary_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diary_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `diary_id` int unsigned NOT NULL,
  `admin_id` int unsigned NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `diary_id` (`diary_id`),
  CONSTRAINT `diary_comments_ibfk_1` FOREIGN KEY (`diary_id`) REFERENCES `work_diary` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diary_comments`
--

LOCK TABLES `diary_comments` WRITE;
/*!40000 ALTER TABLE `diary_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `diary_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fiber_routes`
--

DROP TABLE IF EXISTS `fiber_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiber_routes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `route_data` longtext COMMENT 'JSON encoded polyline',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fiber_routes`
--

LOCK TABLES `fiber_routes` WRITE;
/*!40000 ALTER TABLE `fiber_routes` DISABLE KEYS */;
/*!40000 ALTER TABLE `fiber_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ftth_nodes`
--

DROP TABLE IF EXISTS `ftth_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ftth_nodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(30) DEFAULT 'OLT',
  `ip` varchar(15) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `status` enum('online','offline','unknown') DEFAULT 'unknown',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ftth_nodes`
--

LOCK TABLES `ftth_nodes` WRITE;
/*!40000 ALTER TABLE `ftth_nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ftth_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_access_lists`
--

DROP TABLE IF EXISTS `hotspot_access_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_access_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mac` varchar(17) NOT NULL,
  `type` enum('whitelist','blacklist') DEFAULT 'blacklist',
  `reason` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_access_lists`
--

LOCK TABLES `hotspot_access_lists` WRITE;
/*!40000 ALTER TABLE `hotspot_access_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_access_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_access_logs`
--

DROP TABLE IF EXISTS `hotspot_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_access_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `mac` varchar(17) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `profile_id` int unsigned DEFAULT NULL,
  `login_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `logout_at` datetime DEFAULT NULL,
  `data_used` bigint unsigned DEFAULT '0',
  `action` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'success',
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_access_logs`
--

LOCK TABLES `hotspot_access_logs` WRITE;
/*!40000 ALTER TABLE `hotspot_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_hotels`
--

DROP TABLE IF EXISTS `hotspot_hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_hotels` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_id` int unsigned DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_hotels`
--

LOCK TABLES `hotspot_hotels` WRITE;
/*!40000 ALTER TABLE `hotspot_hotels` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_hotels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_invoices`
--

DROP TABLE IF EXISTS `hotspot_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `profile_id` int unsigned DEFAULT NULL,
  `status` enum('paid','unpaid') DEFAULT 'unpaid',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_invoices`
--

LOCK TABLES `hotspot_invoices` WRITE;
/*!40000 ALTER TABLE `hotspot_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_profiles`
--

DROP TABLE IF EXISTS `hotspot_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_profiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `rate_limit` varchar(50) DEFAULT NULL,
  `session_time` int unsigned DEFAULT NULL COMMENT 'seconds',
  `data_limit` bigint unsigned DEFAULT NULL COMMENT 'bytes',
  `price` decimal(10,2) DEFAULT '0.00',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_profiles`
--

LOCK TABLES `hotspot_profiles` WRITE;
/*!40000 ALTER TABLE `hotspot_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_rooms`
--

DROP TABLE IF EXISTS `hotspot_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_rooms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `hotel_id` int unsigned NOT NULL,
  `room_no` varchar(20) NOT NULL,
  `voucher` varchar(30) DEFAULT NULL,
  `status` enum('available','occupied') DEFAULT 'available',
  PRIMARY KEY (`id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `hotspot_rooms_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotspot_hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_rooms`
--

LOCK TABLES `hotspot_rooms` WRITE;
/*!40000 ALTER TABLE `hotspot_rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_settings`
--

DROP TABLE IF EXISTS `hotspot_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_settings`
--

LOCK TABLES `hotspot_settings` WRITE;
/*!40000 ALTER TABLE `hotspot_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_users`
--

DROP TABLE IF EXISTS `hotspot_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_id` int unsigned DEFAULT NULL,
  `mac` varchar(17) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `hotspot_users_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `hotspot_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_users`
--

LOCK TABLES `hotspot_users` WRITE;
/*!40000 ALTER TABLE `hotspot_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotspot_vouchers`
--

DROP TABLE IF EXISTS `hotspot_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotspot_vouchers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `profile_id` int unsigned DEFAULT NULL,
  `used` tinyint(1) DEFAULT '0',
  `used_by` varchar(64) DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `hotspot_vouchers_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `hotspot_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotspot_vouchers`
--

LOCK TABLES `hotspot_vouchers` WRITE;
/*!40000 ALTER TABLE `hotspot_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `hotspot_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(60) DEFAULT NULL,
  `quantity` int DEFAULT '0',
  `unit` varchar(20) DEFAULT 'pcs',
  `unit_cost` decimal(10,2) DEFAULT '0.00',
  `supplier` varchar(100) DEFAULT NULL,
  `branch_id` int unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `brand` varchar(60) DEFAULT NULL,
  `serial_number` varchar(60) DEFAULT NULL,
  `mac_address` varchar(60) DEFAULT NULL,
  `status` enum('in_stock','issued','faulty') DEFAULT 'in_stock',
  `issued_to_user` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `plan_id` int unsigned DEFAULT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `status` enum('paid','unpaid','partial','cancelled') DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kb_categories`
--

DROP TABLE IF EXISTS `kb_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kb_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kb_categories`
--

LOCK TABLES `kb_categories` WRITE;
/*!40000 ALTER TABLE `kb_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `kb_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `knowledge_base`
--

DROP TABLE IF EXISTS `knowledge_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `knowledge_base` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext,
  `status` enum('published','draft') DEFAULT 'published',
  `views` int unsigned DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `knowledge_base_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `kb_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `knowledge_base`
--

LOCK TABLES `knowledge_base` WRITE;
/*!40000 ALTER TABLE `knowledge_base` DISABLE KEYS */;
/*!40000 ALTER TABLE `knowledge_base` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `source` enum('web','referral','call','walk-in','other') DEFAULT 'web',
  `status` enum('new','contacted','qualified','converted','lost') DEFAULT 'new',
  `assigned_to` int unsigned DEFAULT NULL,
  `notes` text,
  `plan_interest` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `success` tinyint(1) DEFAULT '0',
  `attempted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `attempt_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'failed',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (1,'admin',NULL,0,'2026-05-20 16:07:50','2026-05-20 16:07:50','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(2,'admin',NULL,0,'2026-05-20 16:08:01','2026-05-20 16:08:01','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(3,'admin',NULL,0,'2026-05-20 16:09:35','2026-05-20 16:09:35','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(4,'admin',NULL,0,'2026-05-20 16:12:07','2026-05-20 16:12:07','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(5,'admin',NULL,0,'2026-05-20 16:12:38','2026-05-20 16:12:38','success','103.112.150.52','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36'),(6,'admin',NULL,0,'2026-05-20 21:35:40','2026-05-20 21:35:40','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(7,'admin',NULL,0,'2026-05-20 22:36:23','2026-05-20 22:36:23','success','103.112.150.52','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36'),(8,'admin',NULL,0,'2026-05-20 22:36:39','2026-05-20 22:36:39','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(9,'admin',NULL,0,'2026-05-21 00:23:37','2026-05-21 00:23:37','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(10,'admin',NULL,0,'2026-05-21 01:05:48','2026-05-21 01:05:48','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(11,'admin',NULL,0,'2026-05-21 08:02:31','2026-05-21 08:02:31','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(12,'admin',NULL,0,'2026-05-21 09:03:13','2026-05-21 09:03:13','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(13,'admin',NULL,0,'2026-05-21 09:13:42','2026-05-21 09:13:42','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(14,'admin',NULL,0,'2026-05-21 09:22:59','2026-05-21 09:22:59','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(15,'admin',NULL,0,'2026-05-21 09:42:50','2026-05-21 09:42:50','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(16,'admin',NULL,0,'2026-05-21 10:43:57','2026-05-21 10:43:57','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(17,'admin',NULL,0,'2026-05-21 11:48:50','2026-05-21 11:48:50','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(18,'admin',NULL,0,'2026-05-21 11:49:32','2026-05-21 11:49:32','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(19,'admin',NULL,0,'2026-05-21 13:23:21','2026-05-21 13:23:21','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(20,'admin',NULL,0,'2026-05-21 14:36:58','2026-05-21 14:36:58','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(21,'admin',NULL,0,'2026-05-21 15:04:34','2026-05-21 15:04:34','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(22,'admin',NULL,0,'2026-05-21 15:06:08','2026-05-21 15:06:08','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(23,'admin',NULL,0,'2026-05-21 18:02:23','2026-05-21 18:02:23','success','103.112.150.150','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(24,'admin',NULL,0,'2026-05-21 21:38:57','2026-05-21 21:38:57','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(25,'admin',NULL,0,'2026-05-21 22:40:37','2026-05-21 22:40:37','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(26,'admin',NULL,0,'2026-05-21 23:27:50','2026-05-21 23:27:50','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36'),(27,'admin',NULL,0,'2026-05-22 00:29:42','2026-05-22 00:29:42','success','103.112.150.52','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nas`
--

DROP TABLE IF EXISTS `nas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nasname` varchar(128) NOT NULL,
  `shortname` varchar(32) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'other',
  `ports` int DEFAULT NULL,
  `secret` varchar(60) NOT NULL DEFAULT 'secret',
  `server` varchar(64) DEFAULT NULL,
  `community` varchar(50) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `api_user` varchar(64) DEFAULT NULL,
  `api_pass` varchar(64) DEFAULT NULL,
  `api_port` int DEFAULT '8728',
  `status` tinyint(1) DEFAULT '1',
  `device_type` varchar(32) DEFAULT 'mikrotik',
  `snmp_community` varchar(64) DEFAULT 'public',
  `snmp_version` varchar(10) DEFAULT '2c',
  `snmp_port` int DEFAULT '161',
  `brand` varchar(32) DEFAULT 'generic',
  `pon_ports` int DEFAULT '8',
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nas`
--

LOCK TABLES `nas` WRITE;
/*!40000 ALTER TABLE `nas` DISABLE KEYS */;
INSERT INTO `nas` VALUES (1,'V1600d','V1600d','mikrotik',1812,'','mikrotik',NULL,NULL,'103.112.150.52','Sariful','Sariful27091',9993,1,'olt','public','2c',161,'vsol',4,NULL,NULL,NULL,NULL),(2,'Cudy','Cudy','mikrotik',1812,'123456','mikrotik',NULL,NULL,'10.5.50.1','admin','Sa983106',8728,1,'mikrotik','public','2c',161,'generic',8,22.79992500,89.52749400,NULL,NULL),(3,'P3310C','P3310C','mikrotik',1812,'','mikrotik',NULL,'BDCOM OLT','103.112.150.52','admin','admin',994,1,'olt','public','2c',162,'bdcom',4,NULL,NULL,NULL,NULL),(4,'RB1100','RB1100','mikrotik',1812,'','mikrotik',NULL,NULL,'103.112.150.52','Sariful','Sariful27091',8730,1,'mikrotik','public','2c',161,'generic',8,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `nas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_alerts`
--

DROP TABLE IF EXISTS `network_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_alerts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_ip` varchar(15) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('info','warning','critical') DEFAULT 'warning',
  `message` varchar(255) DEFAULT NULL,
  `status` enum('active','resolved') DEFAULT 'active',
  `resolved` tinyint(1) DEFAULT '0',
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resolved` (`resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_alerts`
--

LOCK TABLES `network_alerts` WRITE;
/*!40000 ALTER TABLE `network_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_faults`
--

DROP TABLE IF EXISTS `network_faults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_faults` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `location` varchar(200) DEFAULT NULL,
  `status` enum('open','in_progress','resolved') DEFAULT 'open',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `assigned_to` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT '0',
  `node_id` int unsigned DEFAULT NULL,
  `fault_type` varchar(50) DEFAULT NULL,
  `severity` varchar(20) DEFAULT 'warning',
  `predicted_lat` decimal(10,8) DEFAULT NULL,
  `predicted_lng` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_faults`
--

LOCK TABLES `network_faults` WRITE;
/*!40000 ALTER TABLE `network_faults` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_faults` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_topology_links`
--

DROP TABLE IF EXISTS `network_topology_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_topology_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_node` varchar(60) NOT NULL,
  `to_node` varchar(60) NOT NULL,
  `link_type` enum('fiber','copper','wifi') DEFAULT 'fiber',
  `label` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_topology_links`
--

LOCK TABLES `network_topology_links` WRITE;
/*!40000 ALTER TABLE `network_topology_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_topology_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `olt_onu_signal`
--

DROP TABLE IF EXISTS `olt_onu_signal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `olt_onu_signal` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `onu_serial` varchar(60) NOT NULL,
  `olt_name` varchar(60) DEFAULT NULL,
  `rx_power` decimal(6,2) DEFAULT NULL COMMENT 'dBm',
  `tx_power` decimal(6,2) DEFAULT NULL COMMENT 'dBm',
  `temperature` decimal(5,2) DEFAULT NULL,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_serial` (`onu_serial`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `olt_onu_signal`
--

LOCK TABLES `olt_onu_signal` WRITE;
/*!40000 ALTER TABLE `olt_onu_signal` DISABLE KEYS */;
INSERT INTO `olt_onu_signal` VALUES (1,'BDCM0000768914','V1600d',-20.00,2.00,36.00,'2026-05-21 15:10:03'),(2,'BDCM0000725985','V1600d',-30.00,4.00,52.00,'2026-05-21 15:10:03'),(3,'BDCM0000534362','V1600d',-25.00,1.00,53.00,'2026-05-21 15:10:03'),(4,'BDCM0000832861','V1600d',-28.00,0.00,54.00,'2026-05-21 15:10:03'),(5,'BDCM0000158301','V1600d',-20.00,3.00,43.00,'2026-05-21 15:10:03'),(6,'BDCM0000950048','V1600d',-29.00,4.00,58.00,'2026-05-21 15:10:03'),(7,'BDCM0000418832','V1600d',-22.00,0.00,58.00,'2026-05-21 15:10:03'),(8,'BDCM0000102974','V1600d',-28.00,5.00,55.00,'2026-05-21 15:10:03'),(9,'BDCM0000263686','V1600d',-30.00,3.00,60.00,'2026-05-21 15:10:03'),(10,'BDCM0000368982','V1600d',-24.00,5.00,57.00,'2026-05-21 15:10:03');
/*!40000 ALTER TABLE `olt_onu_signal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `onu_power_history`
--

DROP TABLE IF EXISTS `onu_power_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `onu_power_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `onu_serial` varchar(60) NOT NULL,
  `rx_power` decimal(6,2) DEFAULT NULL,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onu_power_history`
--

LOCK TABLES `onu_power_history` WRITE;
/*!40000 ALTER TABLE `onu_power_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `onu_power_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_gateways`
--

DROP TABLE IF EXISTS `payment_gateways`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_gateways` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `config` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_gateways`
--

LOCK TABLES `payment_gateways` WRITE;
/*!40000 ALTER TABLE `payment_gateways` DISABLE KEYS */;
INSERT INTO `payment_gateways` VALUES (1,'esewa',0,NULL,'2026-05-20 15:52:30'),(2,'khalti',0,NULL,'2026-05-20 15:52:30'),(3,'stripe',0,NULL,'2026-05-20 15:52:30'),(4,'bank',1,NULL,'2026-05-20 15:52:30');
/*!40000 ALTER TABLE `payment_gateways` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `gateway` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `reference` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `performance_metrics`
--

DROP TABLE IF EXISTS `performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_metrics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_ip` varchar(15) NOT NULL,
  `metric_type` varchar(60) DEFAULT NULL,
  `metric_value` decimal(12,4) DEFAULT NULL,
  `target_type` varchar(32) DEFAULT NULL,
  `metric` varchar(60) NOT NULL,
  `value` decimal(12,4) DEFAULT NULL,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_metric` (`device_ip`,`metric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `performance_metrics`
--

LOCK TABLES `performance_metrics` WRITE;
/*!40000 ALTER TABLE `performance_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `performance_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `speed` varchar(50) NOT NULL COMMENT 'e.g. 100M/100M',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `validity` int NOT NULL DEFAULT '30' COMMENT 'days',
  `data_limit` int DEFAULT '1000' COMMENT 'GB',
  `fup1_speed` varchar(50) DEFAULT NULL,
  `fup1_limit` int DEFAULT NULL COMMENT 'GB',
  `fup2_speed` varchar(50) DEFAULT NULL,
  `fup2_limit` int DEFAULT NULL,
  `fup3_speed` varchar(50) DEFAULT NULL,
  `fup3_limit` int DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Basic 10M','10M/10M',500.00,30,100,NULL,NULL,NULL,NULL,NULL,NULL,'active','2026-05-20 15:52:29'),(2,'Standard 25M','25M/25M',900.00,30,300,NULL,NULL,NULL,NULL,NULL,NULL,'active','2026-05-20 15:52:29'),(3,'Premium 50M','50M/50M',1500.00,30,500,NULL,NULL,NULL,NULL,NULL,NULL,'active','2026-05-20 15:52:29'),(4,'Ultra 100M','100M/100M',2500.00,30,1000,NULL,NULL,NULL,NULL,NULL,NULL,'active','2026-05-20 15:52:29');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `port_assignments`
--

DROP TABLE IF EXISTS `port_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `port_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `device_name` varchar(100) NOT NULL,
  `port` varchar(20) NOT NULL,
  `vlan` smallint unsigned DEFAULT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `port_assignments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `port_assignments`
--

LOCK TABLES `port_assignments` WRITE;
/*!40000 ALTER TABLE `port_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `port_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radacct`
--

DROP TABLE IF EXISTS `radacct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radacct` (
  `radacctid` bigint NOT NULL AUTO_INCREMENT,
  `acctsessionid` varchar(64) NOT NULL DEFAULT '',
  `acctuniqueid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `realm` varchar(64) DEFAULT '',
  `nasipaddress` varchar(15) NOT NULL DEFAULT '',
  `nasportid` varchar(15) DEFAULT NULL,
  `nasporttype` varchar(32) DEFAULT NULL,
  `acctstarttime` datetime DEFAULT NULL,
  `acctupdatetime` datetime DEFAULT NULL,
  `acctstoptime` datetime DEFAULT NULL,
  `acctinterval` int DEFAULT NULL,
  `acctsessiontime` int unsigned DEFAULT NULL,
  `acctauthentic` varchar(32) DEFAULT NULL,
  `connectinfo_start` varchar(50) DEFAULT NULL,
  `connectinfo_stop` varchar(50) DEFAULT NULL,
  `acctinputoctets` bigint DEFAULT NULL,
  `acctoutputoctets` bigint DEFAULT NULL,
  `calledstationid` varchar(50) NOT NULL DEFAULT '',
  `callingstationid` varchar(50) NOT NULL DEFAULT '',
  `acctterminatecause` varchar(32) NOT NULL DEFAULT '',
  `framedprotocol` varchar(32) DEFAULT NULL,
  `framedipaddress` varchar(15) NOT NULL DEFAULT '',
  `acctstartdelay` int DEFAULT NULL,
  `acctstopdelay` int DEFAULT NULL,
  `xascendsessionsvrkey` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`radacctid`),
  UNIQUE KEY `acctuniqueid` (`acctuniqueid`),
  KEY `username` (`username`),
  KEY `framedipaddress` (`framedipaddress`),
  KEY `acctstarttime` (`acctstarttime`),
  KEY `acctstoptime` (`acctstoptime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radacct`
--

LOCK TABLES `radacct` WRITE;
/*!40000 ALTER TABLE `radacct` DISABLE KEYS */;
/*!40000 ALTER TABLE `radacct` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radcheck`
--

DROP TABLE IF EXISTS `radcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radcheck` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT ':=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radcheck`
--

LOCK TABLES `radcheck` WRITE;
/*!40000 ALTER TABLE `radcheck` DISABLE KEYS */;
INSERT INTO `radcheck` VALUES (1,'joshim001','Cleartext-Password',':=','1234'),(2,'joshim001','Expiration',':=','20 Jun 2026');
/*!40000 ALTER TABLE `radcheck` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radpostauth`
--

DROP TABLE IF EXISTS `radpostauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radpostauth` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `pass` varchar(64) NOT NULL DEFAULT '',
  `reply` varchar(32) NOT NULL DEFAULT '',
  `authdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radpostauth`
--

LOCK TABLES `radpostauth` WRITE;
/*!40000 ALTER TABLE `radpostauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `radpostauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radreply`
--

DROP TABLE IF EXISTS `radreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radreply` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(64) NOT NULL DEFAULT '',
  `op` char(2) NOT NULL DEFAULT ':=',
  `value` varchar(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`(32))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radreply`
--

LOCK TABLES `radreply` WRITE;
/*!40000 ALTER TABLE `radreply` DISABLE KEYS */;
INSERT INTO `radreply` VALUES (1,'joshim001','Mikrotik-Rate-Limit',':=','10M/10M');
/*!40000 ALTER TABLE `radreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radusergroup`
--

DROP TABLE IF EXISTS `radusergroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radusergroup` (
  `username` varchar(64) NOT NULL DEFAULT '',
  `groupname` varchar(64) NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '1',
  KEY `username` (`username`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radusergroup`
--

LOCK TABLES `radusergroup` WRITE;
/*!40000 ALTER TABLE `radusergroup` DISABLE KEYS */;
INSERT INTO `radusergroup` VALUES ('joshim001','Basic 10M',1);
/*!40000 ALTER TABLE `radusergroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recharge`
--

DROP TABLE IF EXISTS `recharge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recharge` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `plan_id` int unsigned DEFAULT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `method` varchar(50) DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `recharged_by` int unsigned DEFAULT NULL COMMENT 'admin id',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recharge`
--

LOCK TABLES `recharge` WRITE;
/*!40000 ALTER TABLE `recharge` DISABLE KEYS */;
/*!40000 ALTER TABLE `recharge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recipient` varchar(20) NOT NULL,
  `message` text,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_logs`
--

LOCK TABLES `sms_logs` WRITE;
/*!40000 ALTER TABLE `sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_config`
--

DROP TABLE IF EXISTS `system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(80) NOT NULL,
  `value` text,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_config`
--

LOCK TABLES `system_config` WRITE;
/*!40000 ALTER TABLE `system_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'app_name','ISP Management System',NULL),(2,'currency','BDT',NULL),(3,'tax_rate','15',NULL),(4,'session_timeout','60',NULL),(5,'session_idle_timeout','30',NULL),(6,'sms_api_key','',NULL),(7,'smtp_host','',NULL),(8,'smtp_user','',NULL),(9,'smtp_pass','',NULL),(10,'smtp_port','587',NULL),(11,'company_name','My ISP Company',NULL),(12,'company_phone','',NULL),(13,'company_email','',NULL),(14,'company_address','',NULL),(17,'max_login_attempts','5',NULL),(18,'lockout_duration','15',NULL),(19,'expire_block_time','08:00',NULL),(20,'tax_tsc_pct','13.00',NULL),(21,'tax_vat_pct','13.00',NULL),(22,'theme','dark',NULL);
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_replies`
--

DROP TABLE IF EXISTS `ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_replies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `author_type` enum('admin','customer') DEFAULT 'admin',
  `message` text,
  `is_internal` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_replies`
--

LOCK TABLES `ticket_replies` WRITE;
/*!40000 ALTER TABLE `ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `assigned_to` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uptime_logs`
--

DROP TABLE IF EXISTS `uptime_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uptime_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_ip` varchar(15) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `status` enum('up','down') DEFAULT 'up',
  `checked_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uptime_logs`
--

LOCK TABLES `uptime_logs` WRITE;
/*!40000 ALTER TABLE `uptime_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `uptime_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_logs`
--

DROP TABLE IF EXISTS `usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usage_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `upload` bigint unsigned DEFAULT '0',
  `download` bigint unsigned DEFAULT '0',
  `session_id` varchar(64) DEFAULT NULL,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_logs`
--

LOCK TABLES `usage_logs` WRITE;
/*!40000 ALTER TABLE `usage_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `usage_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wire_leases`
--

DROP TABLE IF EXISTS `wire_leases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wire_leases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int unsigned DEFAULT NULL,
  `mac_address` varchar(17) NOT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `lease_start` datetime DEFAULT NULL,
  `lease_end` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `wire_leases_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wire_leases`
--

LOCK TABLES `wire_leases` WRITE;
/*!40000 ALTER TABLE `wire_leases` DISABLE KEYS */;
/*!40000 ALTER TABLE `wire_leases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_diary`
--

DROP TABLE IF EXISTS `work_diary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_diary` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `work_date` date NOT NULL,
  `status` enum('pending','in_progress','done') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(50) DEFAULT 'General',
  `content` text,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_diary`
--

LOCK TABLES `work_diary` WRITE;
/*!40000 ALTER TABLE `work_diary` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_diary` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-22  1:08:07
