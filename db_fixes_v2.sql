-- ============================================================
--  ISP System — Missing Tables & Columns Fix Script v2
--  MySQL 8.0 compatible (no ADD COLUMN IF NOT EXISTS)
-- ============================================================

-- ============================================================
-- 1. branches — add: code
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='branches' AND COLUMN_NAME='code');
SET @sql := IF(@exist=0, 
  'ALTER TABLE `branches` ADD COLUMN `code` VARCHAR(20) DEFAULT NULL AFTER `name`',
  'SELECT "branches.code already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. tickets — add: branch_id
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='tickets' AND COLUMN_NAME='branch_id');
SET @sql := IF(@exist=0,
  'ALTER TABLE `tickets` ADD COLUMN `branch_id` INT UNSIGNED DEFAULT NULL AFTER `customer_id`',
  'SELECT "tickets.branch_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. recharge — add: months
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='recharge' AND COLUMN_NAME='months');
SET @sql := IF(@exist=0,
  'ALTER TABLE `recharge` ADD COLUMN `months` TINYINT UNSIGNED DEFAULT 1 AFTER `amount`',
  'SELECT "recharge.months already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 4. network_alerts — add: device_id, status
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='network_alerts' AND COLUMN_NAME='device_id');
SET @sql := IF(@exist=0,
  'ALTER TABLE `network_alerts` ADD COLUMN `device_id` INT UNSIGNED DEFAULT NULL AFTER `id`',
  'SELECT "network_alerts.device_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='network_alerts' AND COLUMN_NAME='status');
SET @sql := IF(@exist=0,
  'ALTER TABLE `network_alerts` ADD COLUMN `status` VARCHAR(20) DEFAULT ''active'' AFTER `message`',
  'SELECT "network_alerts.status already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. hotspot_rooms — add: room_number, floor, mac_address
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_rooms' AND COLUMN_NAME='room_number');
SET @sql := IF(@exist=0,
  'ALTER TABLE `hotspot_rooms` ADD COLUMN `room_number` VARCHAR(20) DEFAULT NULL AFTER `hotel_id`',
  'SELECT "hotspot_rooms.room_number already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_rooms' AND COLUMN_NAME='floor');
SET @sql := IF(@exist=0,
  'ALTER TABLE `hotspot_rooms` ADD COLUMN `floor` VARCHAR(10) DEFAULT NULL AFTER `room_number`',
  'SELECT "hotspot_rooms.floor already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_rooms' AND COLUMN_NAME='mac_address');
SET @sql := IF(@exist=0,
  'ALTER TABLE `hotspot_rooms` ADD COLUMN `mac_address` VARCHAR(17) DEFAULT NULL AFTER `floor`',
  'SELECT "hotspot_rooms.mac_address already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 6. hotspot_users — add: mac_address
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='hotspot_users' AND COLUMN_NAME='mac_address');
SET @sql := IF(@exist=0,
  'ALTER TABLE `hotspot_users` ADD COLUMN `mac_address` VARCHAR(17) DEFAULT NULL AFTER `mac`',
  'SELECT "hotspot_users.mac_address already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 7. activity_log — add: user_id, username, ip_address
-- ============================================================
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='activity_log' AND COLUMN_NAME='user_id');
SET @sql := IF(@exist=0,
  'ALTER TABLE `activity_log` ADD COLUMN `user_id` INT UNSIGNED DEFAULT NULL AFTER `id`',
  'SELECT "activity_log.user_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='activity_log' AND COLUMN_NAME='username');
SET @sql := IF(@exist=0,
  'ALTER TABLE `activity_log` ADD COLUMN `username` VARCHAR(60) DEFAULT NULL AFTER `user_id`',
  'SELECT "activity_log.username already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA='isp_db' AND TABLE_NAME='activity_log' AND COLUMN_NAME='ip_address');
SET @sql := IF(@exist=0,
  'ALTER TABLE `activity_log` ADD COLUMN `ip_address` VARCHAR(45) DEFAULT NULL AFTER `ip`',
  'SELECT "activity_log.ip_address already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 8. wallet_transactions — create table if not exists
-- ============================================================
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(60)   NOT NULL,
  `amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
  `gateway`    VARCHAR(50)   DEFAULT NULL,
  `status`     VARCHAR(20)   DEFAULT 'pending',
  `txn_id`     VARCHAR(100)  DEFAULT NULL,
  `note`       TEXT          DEFAULT NULL,
  `created_at` DATETIME      DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Final verification — should show all 11 rows
-- ============================================================
SELECT TABLE_NAME AS `table`, COLUMN_NAME AS `column`, 'OK' AS status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'isp_db' AND (
  (TABLE_NAME='branches'          AND COLUMN_NAME='code')         OR
  (TABLE_NAME='tickets'           AND COLUMN_NAME='branch_id')    OR
  (TABLE_NAME='recharge'          AND COLUMN_NAME='months')       OR
  (TABLE_NAME='network_alerts'    AND COLUMN_NAME='device_id')    OR
  (TABLE_NAME='network_alerts'    AND COLUMN_NAME='status')       OR
  (TABLE_NAME='hotspot_rooms'     AND COLUMN_NAME='room_number')  OR
  (TABLE_NAME='hotspot_rooms'     AND COLUMN_NAME='floor')        OR
  (TABLE_NAME='hotspot_rooms'     AND COLUMN_NAME='mac_address')  OR
  (TABLE_NAME='hotspot_users'     AND COLUMN_NAME='mac_address')  OR
  (TABLE_NAME='activity_log'      AND COLUMN_NAME='user_id')      OR
  (TABLE_NAME='activity_log'      AND COLUMN_NAME='username')     OR
  (TABLE_NAME='activity_log'      AND COLUMN_NAME='ip_address')   OR
  (TABLE_NAME='wallet_transactions' AND COLUMN_NAME='id')
)
ORDER BY TABLE_NAME, COLUMN_NAME;
