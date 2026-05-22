-- ============================================================
--  ISP Management System - Database Schema
--  Version: 2.0.0
--  Charset: utf8mb4_unicode_ci
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `isp_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `isp_db`;

-- ============================================================
--  BRANCHES
-- ============================================================
CREATE TABLE IF NOT EXISTS `branches` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `address`    VARCHAR(255) DEFAULT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `status`     ENUM('active','inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `branches` (`name`,`address`,`phone`,`status`) VALUES
('Main Branch','Dhaka, Bangladesh','01700000000','active');

-- ============================================================
--  ADMIN USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`    VARCHAR(60)  NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('superadmin','manager','support') DEFAULT 'support',
  `branch_id`   INT UNSIGNED DEFAULT NULL,
  `email`       VARCHAR(100) DEFAULT NULL,
  `last_login`  DATETIME DEFAULT NULL,
  `status`      ENUM('active','inactive') DEFAULT 'active',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username=admin  password=admin123
INSERT INTO `admins` (`username`,`password`,`role`,`branch_id`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 1);

-- ============================================================
--  ROLES & PERMISSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id`    INT UNSIGNED NOT NULL,
  `permission` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  SERVICE PLANS
-- ============================================================
CREATE TABLE IF NOT EXISTS `plans` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`           VARCHAR(100) NOT NULL,
  `speed`          VARCHAR(50)  NOT NULL COMMENT 'e.g. 100M/100M',
  `price`          DECIMAL(10,2) NOT NULL DEFAULT 0,
  `validity`       INT NOT NULL DEFAULT 30 COMMENT 'days',
  `data_limit`     INT DEFAULT 1000 COMMENT 'GB',
  `fup1_speed`     VARCHAR(50)  DEFAULT NULL,
  `fup1_limit`     INT DEFAULT NULL COMMENT 'GB',
  `fup2_speed`     VARCHAR(50)  DEFAULT NULL,
  `fup2_limit`     INT DEFAULT NULL COMMENT 'GB',
  `fup3_speed`     VARCHAR(50)  DEFAULT NULL,
  `fup3_limit`     INT DEFAULT NULL COMMENT 'GB',
  `status`         ENUM('active','inactive') DEFAULT 'active',
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `plans` (`name`,`speed`,`price`,`validity`,`data_limit`) VALUES
('Basic 10M',   '10M/10M',   500.00,  30, 100),
('Standard 25M','25M/25M',   900.00,  30, 300),
('Premium 50M', '50M/50M',   1500.00, 30, 500),
('Ultra 100M',  '100M/100M', 2500.00, 30, 1000);

-- ============================================================
--  CUSTOMERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`      VARCHAR(60)  NOT NULL UNIQUE,
  `password`      VARCHAR(255) NOT NULL,
  `full_name`     VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(20)  DEFAULT NULL,
  `email`         VARCHAR(100) DEFAULT NULL,
  `address`       VARCHAR(255) DEFAULT NULL,
  `lat`           DECIMAL(10,8) DEFAULT NULL,
  `lng`           DECIMAL(11,8) DEFAULT NULL,
  `plan_id`       INT UNSIGNED DEFAULT NULL,
  `branch_id`     INT UNSIGNED DEFAULT NULL,
  `expiry`        DATE NOT NULL,
  `status`        ENUM('active','inactive','suspended','pending') DEFAULT 'active',
  `onu_serial`    VARCHAR(60)  DEFAULT NULL,
  `vlan`          SMALLINT UNSIGNED DEFAULT NULL,
  `olt`           VARCHAR(60)  DEFAULT NULL,
  `olt_port`      TINYINT UNSIGNED DEFAULT NULL,
  `master_box`    VARCHAR(60)  DEFAULT NULL,
  `db_box`        VARCHAR(60)  DEFAULT NULL,
  `db_port`       TINYINT UNSIGNED DEFAULT NULL,
  `wifi_ssid`     VARCHAR(60)  DEFAULT NULL,
  `wifi_password` VARCHAR(60)  DEFAULT NULL,
  `wallet_balance` DECIMAL(10,2) DEFAULT 0,
  `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`plan_id`)    REFERENCES `plans`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`branch_id`)  REFERENCES `branches`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_expiry` (`expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  RADIUS TABLES (FreeRADIUS compatible)
-- ============================================================
CREATE TABLE IF NOT EXISTS `radcheck` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`  VARCHAR(64) NOT NULL DEFAULT '',
  `attribute` VARCHAR(64) NOT NULL DEFAULT '',
  `op`        CHAR(2)     NOT NULL DEFAULT ':=',
  `value`     VARCHAR(253) NOT NULL DEFAULT '',
  INDEX `username` (`username`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `radreply` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`  VARCHAR(64) NOT NULL DEFAULT '',
  `attribute` VARCHAR(64) NOT NULL DEFAULT '',
  `op`        CHAR(2)     NOT NULL DEFAULT ':=',
  `value`     VARCHAR(253) NOT NULL DEFAULT '',
  INDEX `username` (`username`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `radusergroup` (
  `username`  VARCHAR(64) NOT NULL DEFAULT '',
  `groupname` VARCHAR(64) NOT NULL DEFAULT '',
  `priority`  INT NOT NULL DEFAULT 1,
  INDEX `username` (`username`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `radacct` (
  `radacctid`            BIGINT AUTO_INCREMENT PRIMARY KEY,
  `acctsessionid`        VARCHAR(64) NOT NULL DEFAULT '',
  `acctuniqueid`         VARCHAR(32) NOT NULL DEFAULT '',
  `username`             VARCHAR(64) NOT NULL DEFAULT '',
  `groupname`            VARCHAR(64) NOT NULL DEFAULT '',
  `realm`                VARCHAR(64) DEFAULT '',
  `nasipaddress`         VARCHAR(15) NOT NULL DEFAULT '',
  `nasportid`            VARCHAR(15) DEFAULT NULL,
  `nasporttype`          VARCHAR(32) DEFAULT NULL,
  `acctstarttime`        DATETIME DEFAULT NULL,
  `acctupdatetime`       DATETIME DEFAULT NULL,
  `acctstoptime`         DATETIME DEFAULT NULL,
  `acctinterval`         INT DEFAULT NULL,
  `acctsessiontime`      INT UNSIGNED DEFAULT NULL,
  `acctauthentic`        VARCHAR(32) DEFAULT NULL,
  `connectinfo_start`    VARCHAR(50) DEFAULT NULL,
  `connectinfo_stop`     VARCHAR(50) DEFAULT NULL,
  `acctinputoctets`      BIGINT DEFAULT NULL,
  `acctoutputoctets`     BIGINT DEFAULT NULL,
  `calledstationid`      VARCHAR(50) NOT NULL DEFAULT '',
  `callingstationid`     VARCHAR(50) NOT NULL DEFAULT '',
  `acctterminatecause`   VARCHAR(32) NOT NULL DEFAULT '',
  `framedprotocol`       VARCHAR(32) DEFAULT NULL,
  `framedipaddress`      VARCHAR(15) NOT NULL DEFAULT '',
  `acctstartdelay`       INT DEFAULT NULL,
  `acctstopdelay`        INT DEFAULT NULL,
  `xascendsessionsvrkey` VARCHAR(10) DEFAULT NULL,
  INDEX `username`        (`username`),
  INDEX `framedipaddress` (`framedipaddress`),
  INDEX `acctstarttime`   (`acctstarttime`),
  INDEX `acctstoptime`    (`acctstoptime`),
  UNIQUE KEY `acctuniqueid` (`acctuniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `radpostauth` (
  `id`       INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(64) NOT NULL DEFAULT '',
  `pass`     VARCHAR(64) NOT NULL DEFAULT '',
  `reply`    VARCHAR(32) NOT NULL DEFAULT '',
  `authdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  NAS (Network Access Servers)
-- ============================================================
CREATE TABLE IF NOT EXISTS `nas` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nasname`        VARCHAR(128) NOT NULL,
  `shortname`      VARCHAR(32)  DEFAULT NULL,
  `type`           VARCHAR(30)  DEFAULT 'mikrotik',
  `ports`          INT          DEFAULT 1812,
  `secret`         VARCHAR(60)  NOT NULL DEFAULT 'secret',
  `server`         VARCHAR(64)  DEFAULT 'mikrotik',
  `community`      VARCHAR(50)  DEFAULT 'public',
  `description`    VARCHAR(200) DEFAULT NULL,
  `ip_address`     VARCHAR(45)  DEFAULT NULL,
  `api_user`       VARCHAR(64)  DEFAULT NULL,
  `api_pass`       VARCHAR(64)  DEFAULT NULL,
  `api_port`       INT          DEFAULT 8728,
  `status`         TINYINT(1)   DEFAULT 1,
  `device_type`    VARCHAR(32)  DEFAULT 'mikrotik',
  `snmp_community` VARCHAR(64)  DEFAULT 'public',
  `snmp_version`   VARCHAR(10)  DEFAULT '2c',
  `brand`          VARCHAR(32)  DEFAULT 'generic',
  `pon_ports`      INT          DEFAULT 8,
  `model`          VARCHAR(64)  DEFAULT NULL,
  `location`       VARCHAR(255) DEFAULT NULL,
  `lat`            DECIMAL(10,8) DEFAULT NULL,
  `lng`            DECIMAL(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  INVOICES & RECHARGE
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `username`    VARCHAR(60)  NOT NULL,
  `amount`      DECIMAL(10,2) NOT NULL,
  `plan_id`     INT UNSIGNED DEFAULT NULL,
  `plan_name`   VARCHAR(100) DEFAULT NULL,
  `status`      ENUM('paid','unpaid','partial','cancelled') DEFAULT 'unpaid',
  `due_date`    DATE DEFAULT NULL,
  `paid_at`     DATETIME DEFAULT NULL,
  `note`        VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  INDEX `idx_username` (`username`),
  INDEX `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `recharge` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id`  INT UNSIGNED DEFAULT NULL,
  `username`     VARCHAR(60)  NOT NULL,
  `amount`       DECIMAL(10,2) NOT NULL,
  `plan_id`      INT UNSIGNED DEFAULT NULL,
  `plan_name`    VARCHAR(100) DEFAULT NULL,
  `method`       VARCHAR(50)  DEFAULT 'cash',
  `reference`    VARCHAR(100) DEFAULT NULL,
  `recharged_by` INT UNSIGNED DEFAULT NULL COMMENT 'admin id',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  BILLING MODULE
-- ============================================================
CREATE TABLE IF NOT EXISTS `billing_invoices` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_number`  VARCHAR(30) NOT NULL UNIQUE,
  `customer_id`     INT UNSIGNED DEFAULT NULL,
  `amount`          DECIMAL(10,2) NOT NULL,
  `tax_amount`      DECIMAL(10,2) DEFAULT 0,
  `discount`        DECIMAL(10,2) DEFAULT 0,
  `total`           DECIMAL(10,2) NOT NULL,
  `status`          ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `due_date`        DATE DEFAULT NULL,
  `paid_at`         DATETIME DEFAULT NULL,
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_subscriptions` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id`    INT UNSIGNED NOT NULL,
  `plan_id`        INT UNSIGNED NOT NULL,
  `start_date`     DATE NOT NULL,
  `end_date`       DATE NOT NULL,
  `status`         ENUM('active','cancelled','expired') DEFAULT 'active',
  `auto_renew`     TINYINT(1) DEFAULT 1,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`)     REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_gateways` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(50) NOT NULL,
  `is_active`  TINYINT(1) DEFAULT 0,
  `config`     JSON DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payment_gateways` (`name`,`is_active`) VALUES
('esewa',  0),
('khalti', 0),
('stripe', 0),
('bank',   1);

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id`  INT UNSIGNED DEFAULT NULL,
  `invoice_id`   INT UNSIGNED DEFAULT NULL,
  `gateway`      VARCHAR(30)  NOT NULL,
  `amount`       DECIMAL(10,2) NOT NULL,
  `status`       ENUM('pending','success','failed','refunded') DEFAULT 'pending',
  `reference`    VARCHAR(150) DEFAULT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  TICKETS (Help Desk)
-- ============================================================
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `subject`     VARCHAR(200) NOT NULL,
  `message`     TEXT,
  `priority`    ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status`      ENUM('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `assigned_to` INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_id`  INT UNSIGNED NOT NULL,
  `author_id`  INT UNSIGNED DEFAULT NULL,
  `author_type` ENUM('admin','customer') DEFAULT 'admin',
  `message`    TEXT,
  `is_internal` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  KNOWLEDGE BASE
-- ============================================================
CREATE TABLE IF NOT EXISTS `kb_categories` (
  `id`   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `content`     LONGTEXT,
  `status`      ENUM('published','draft') DEFAULT 'published',
  `views`       INT UNSIGNED DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `kb_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  NETWORK / MONITORING
-- ============================================================
CREATE TABLE IF NOT EXISTS `network_alerts` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `device_ip`    VARCHAR(15)  NOT NULL,
  `device_name`  VARCHAR(100) DEFAULT NULL,
  `alert_type`   VARCHAR(50)  NOT NULL,
  `severity`     ENUM('info','warning','critical') DEFAULT 'warning',
  `message`      VARCHAR(255) DEFAULT NULL,
  `resolved`     TINYINT(1)  DEFAULT 0,
  `resolved_at`  DATETIME DEFAULT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_resolved` (`resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `network_faults` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) NOT NULL,
  `description` TEXT,
  `location`    VARCHAR(200) DEFAULT NULL,
  `status`      ENUM('open','in_progress','resolved') DEFAULT 'open',
  `priority`    ENUM('low','medium','high','critical') DEFAULT 'medium',
  `assigned_to` INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `network_topology_links` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `from_node` VARCHAR(60) NOT NULL,
  `to_node`   VARCHAR(60) NOT NULL,
  `link_type` ENUM('fiber','copper','wifi') DEFAULT 'fiber',
  `label`     VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ftth_nodes` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `type`       VARCHAR(30)  DEFAULT 'OLT',
  `ip`         VARCHAR(15)  DEFAULT NULL,
  `lat`        DECIMAL(10,8) DEFAULT NULL,
  `lng`        DECIMAL(11,8) DEFAULT NULL,
  `status`     ENUM('online','offline','unknown') DEFAULT 'unknown',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fiber_routes` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `route_data` LONGTEXT COMMENT 'JSON encoded polyline',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `olt_onu_signal` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `onu_serial`  VARCHAR(60) NOT NULL,
  `olt_name`    VARCHAR(60) DEFAULT NULL,
  `rx_power`    DECIMAL(6,2) DEFAULT NULL COMMENT 'dBm',
  `tx_power`    DECIMAL(6,2) DEFAULT NULL COMMENT 'dBm',
  `temperature` DECIMAL(5,2) DEFAULT NULL,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_serial` (`onu_serial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `onu_power_history` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `onu_serial` VARCHAR(60) NOT NULL,
  `rx_power`   DECIMAL(6,2) DEFAULT NULL,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `uptime_logs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `device_ip`   VARCHAR(15) NOT NULL,
  `device_name` VARCHAR(100) DEFAULT NULL,
  `status`      ENUM('up','down') DEFAULT 'up',
  `checked_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `performance_metrics` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `device_ip`   VARCHAR(15)  NOT NULL,
  `metric`      VARCHAR(60)  NOT NULL,
  `value`       DECIMAL(12,4) DEFAULT NULL,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_device_metric` (`device_ip`, `metric`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  HOTSPOT
-- ============================================================
CREATE TABLE IF NOT EXISTS `hotspot_profiles` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(100) NOT NULL,
  `rate_limit`   VARCHAR(50)  DEFAULT NULL,
  `session_time` INT UNSIGNED DEFAULT NULL COMMENT 'seconds',
  `data_limit`   BIGINT UNSIGNED DEFAULT NULL COMMENT 'bytes',
  `price`        DECIMAL(10,2) DEFAULT 0,
  `status`       ENUM('active','inactive') DEFAULT 'active',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(64)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `profile_id` INT UNSIGNED DEFAULT NULL,
  `mac`        VARCHAR(17)  DEFAULT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `status`     ENUM('active','inactive','blocked') DEFAULT 'active',
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`profile_id`) REFERENCES `hotspot_profiles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_vouchers` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`       VARCHAR(30)  NOT NULL UNIQUE,
  `profile_id` INT UNSIGNED DEFAULT NULL,
  `used`       TINYINT(1)  DEFAULT 0,
  `used_by`    VARCHAR(64) DEFAULT NULL,
  `used_at`    DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`profile_id`) REFERENCES `hotspot_profiles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_access_logs` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(64) NOT NULL,
  `mac`        VARCHAR(17) DEFAULT NULL,
  `ip`         VARCHAR(15) DEFAULT NULL,
  `profile_id` INT UNSIGNED DEFAULT NULL,
  `login_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `logout_at`  DATETIME DEFAULT NULL,
  `data_used`  BIGINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_access_lists` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mac`        VARCHAR(17) NOT NULL UNIQUE,
  `type`       ENUM('whitelist','blacklist') DEFAULT 'blacklist',
  `reason`     VARCHAR(200) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_hotels` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `address`    VARCHAR(255) DEFAULT NULL,
  `profile_id` INT UNSIGNED DEFAULT NULL,
  `status`     ENUM('active','inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_rooms` (
  `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT UNSIGNED NOT NULL,
  `room_no`  VARCHAR(20) NOT NULL,
  `voucher`  VARCHAR(30) DEFAULT NULL,
  `status`   ENUM('available','occupied') DEFAULT 'available',
  FOREIGN KEY (`hotel_id`) REFERENCES `hotspot_hotels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_settings` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key`  VARCHAR(60) NOT NULL UNIQUE,
  `setting_value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hotspot_invoices` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`    VARCHAR(64) NOT NULL,
  `amount`      DECIMAL(10,2) NOT NULL,
  `profile_id`  INT UNSIGNED DEFAULT NULL,
  `status`      ENUM('paid','unpaid') DEFAULT 'unpaid',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  LEADS & SALES
-- ============================================================
CREATE TABLE IF NOT EXISTS `leads` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name`    VARCHAR(150) NOT NULL,
  `phone`        VARCHAR(20)  DEFAULT NULL,
  `email`        VARCHAR(100) DEFAULT NULL,
  `address`      VARCHAR(255) DEFAULT NULL,
  `source`       ENUM('web','referral','call','walk-in','other') DEFAULT 'web',
  `status`       ENUM('new','contacted','qualified','converted','lost') DEFAULT 'new',
  `assigned_to`  INT UNSIGNED DEFAULT NULL,
  `notes`        TEXT,
  `plan_interest` INT UNSIGNED DEFAULT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  INVENTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `category`     VARCHAR(60)  DEFAULT NULL,
  `quantity`     INT DEFAULT 0,
  `unit`         VARCHAR(20)  DEFAULT 'pcs',
  `unit_cost`    DECIMAL(10,2) DEFAULT 0,
  `supplier`     VARCHAR(100) DEFAULT NULL,
  `branch_id`    INT UNSIGNED DEFAULT NULL,
  `notes`        VARCHAR(255) DEFAULT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  WORK DIARY
-- ============================================================
CREATE TABLE IF NOT EXISTS `work_diary` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id`    INT UNSIGNED NOT NULL,
  `title`       VARCHAR(200) NOT NULL,
  `description` TEXT,
  `work_date`   DATE NOT NULL,
  `status`      ENUM('pending','in_progress','done') DEFAULT 'pending',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `diary_comments` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `diary_id`   INT UNSIGNED NOT NULL,
  `admin_id`   INT UNSIGNED NOT NULL,
  `comment`    TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`diary_id`) REFERENCES `work_diary`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  PORT ASSIGNMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `port_assignments` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `device_name` VARCHAR(100) NOT NULL,
  `port`        VARCHAR(20)  NOT NULL,
  `vlan`        SMALLINT UNSIGNED DEFAULT NULL,
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  WIRE LEASES
-- ============================================================
CREATE TABLE IF NOT EXISTS `wire_leases` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `mac_address` VARCHAR(17)  NOT NULL,
  `ip_address`  VARCHAR(15)  DEFAULT NULL,
  `lease_start` DATETIME DEFAULT NULL,
  `lease_end`   DATETIME DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  DATA USAGE / LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `data_usage` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `upload`      BIGINT UNSIGNED DEFAULT 0,
  `download`    BIGINT UNSIGNED DEFAULT 0,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `usage_logs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`    VARCHAR(64) NOT NULL,
  `upload`      BIGINT UNSIGNED DEFAULT 0,
  `download`    BIGINT UNSIGNED DEFAULT 0,
  `session_id`  VARCHAR(64) DEFAULT NULL,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  MESSAGING / SMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `recipient`   VARCHAR(20)  NOT NULL,
  `message`     TEXT,
  `status`      ENUM('sent','failed','pending') DEFAULT 'pending',
  `sent_at`     DATETIME DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  SYSTEM LOGS & SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id`    INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `description` TEXT,
  `ip`          VARCHAR(45) DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin`  (`admin_id`),
  INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`    VARCHAR(64) NOT NULL,
  `ip`          VARCHAR(45) DEFAULT NULL,
  `success`     TINYINT(1) DEFAULT 0,
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key`   VARCHAR(80)  NOT NULL UNIQUE,
  `setting_value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('app_name',            'ISP Management System'),
('currency',            'BDT'),
('tax_rate',            '15'),
('session_timeout',     '60'),
('session_idle_timeout','30'),
('sms_api_key',         ''),
('smtp_host',           ''),
('smtp_user',           ''),
('smtp_pass',           ''),
('smtp_port',           '587'),
('company_name',        'My ISP Company'),
('company_phone',       ''),
('company_email',       ''),
('company_address',     '');

CREATE TABLE IF NOT EXISTS `system_config` (
  `id`     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`    VARCHAR(80) NOT NULL UNIQUE,
  `value`  TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `auto_invoice_log` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `invoice_id`  INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  END OF SCHEMA
-- ============================================================
